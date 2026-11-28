<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class RegionSeeder extends Seeder
{
    private const API_BASE_URL = 'https://www.emsifa.com/api-wilayah-indonesia/v2';

    public function run(): void
    {
        $this->command->info('Memeriksa statistik data wilayah Indonesia...');

        $stats = $this->fetchData(self::API_BASE_URL . '/stats.json');
        $expectedProvinceCount = (int) ($stats['total_provinces'] ?? 0);
        $expectedRegencyCount = (int) ($stats['total_regencies'] ?? 0);

        if ($expectedProvinceCount !== 38 || $expectedRegencyCount < 1) {
            throw new RuntimeException(
                "Statistik API tidak sesuai. Provinsi: {$expectedProvinceCount}, kabupaten/kota: {$expectedRegencyCount}."
            );
        }

        $this->command->info('Mengambil daftar provinsi...');
        $provinces = $this->fetchData(self::API_BASE_URL . '/provinces.json');

        if (!array_is_list($provinces) || count($provinces) !== $expectedProvinceCount) {
            throw new RuntimeException('Daftar provinsi dari API tidak lengkap atau format respons berubah.');
        }

        $provinceRows = [];
        $regencyRows = [];

        foreach ($provinces as $province) {
            if (!is_array($province) || !isset($province['id'], $province['name'])) {
                throw new RuntimeException('Ditemukan data provinsi dengan format tidak valid.');
            }

            $provinceId = (string) $province['id'];
            $provinceName = Str::upper(trim((string) $province['name']));

            if (!preg_match('/^\d{2}$/D', $provinceId) || $provinceName === '') {
                throw new RuntimeException("ID atau nama provinsi tidak valid: {$provinceId}.");
            }

            $provinceRows[] = [
                'id' => $provinceId,
                'name' => $provinceName,
            ];

            $this->command->info("Mengambil kabupaten/kota untuk provinsi: {$provinceName}");

            $regencies = $this->fetchData(
                self::API_BASE_URL . '/regencies/' . $provinceId . '.json'
            );

            if (!array_is_list($regencies)) {
                throw new RuntimeException("Format kabupaten/kota untuk provinsi {$provinceId} tidak valid.");
            }

            foreach ($regencies as $regency) {
                if (!is_array($regency) || !isset($regency['id'], $regency['name'])) {
                    throw new RuntimeException("Ditemukan data kabupaten/kota tidak valid pada provinsi {$provinceId}.");
                }

                $apiRegencyId = (string) $regency['id'];
                $regencyName = Str::upper(trim((string) $regency['name']));

                if (
                    !preg_match('/^(\d{2})\.(\d{2})$/D', $apiRegencyId, $matches)
                    || $matches[1] !== $provinceId
                    || $regencyName === ''
                ) {
                    throw new RuntimeException("Kode kabupaten/kota tidak valid: {$apiRegencyId}.");
                }

                // API v2 uses dotted IDs (e.g. 11.01). The existing schema
                // uses the equivalent four-character legacy form (1101).
                $legacyRegencyId = str_replace('.', '', $apiRegencyId);

                $regencyRows[] = [
                    'id' => $legacyRegencyId,
                    'province_id' => $provinceId,
                    'name' => $regencyName,
                ];
            }
        }

        $provinceIds = array_column($provinceRows, 'id');
        $regencyIds = array_column($regencyRows, 'id');

        if (
            count(array_unique($provinceIds)) !== $expectedProvinceCount
            || count($regencyRows) !== $expectedRegencyCount
            || count(array_unique($regencyIds)) !== $expectedRegencyCount
        ) {
            throw new RuntimeException(
                'Validasi jumlah atau keunikan data gagal. Tidak ada perubahan database yang dilakukan.'
            );
        }

        $newProvincesByName = [];
        foreach ($provinceRows as $row) {
            $newProvincesByName[$this->normalizeName($row['name'])][] = $row;
        }

        $newRegenciesByName = [];
        foreach ($regencyRows as $row) {
            $newRegenciesByName[$this->normalizeName($row['name'])][] = $row;
        }

        $this->command->info(
            "Validasi sumber selesai: {$expectedProvinceCount} provinsi dan {$expectedRegencyCount} kabupaten/kota."
        );
        $this->command->info('Memperbarui data wilayah dan menyesuaikan referensi profil pengguna...');

        // Build and validate every user-location remapping before writing.
        // This is important because the Papua province/regency codes changed
        // after the regional split. Existing user locations are remapped by name.
        $result = DB::transaction(function () use (
            $provinceRows,
            $regencyRows,
            $provinceIds,
            $regencyIds,
            $newProvincesByName,
            $newRegenciesByName
        ): array {
            $existingProvinces = DB::table('provinces')
                ->get(['id', 'name'])
                ->keyBy(fn ($row) => (string) $row->id);

            $existingRegencies = DB::table('regencies')
                ->get(['id', 'province_id', 'name'])
                ->keyBy(fn ($row) => (string) $row->id);

            $userUpdates = [];

            foreach (DB::table('users')->get(['id', 'province_id', 'city_id']) as $user) {
                $oldProvinceId = $user->province_id !== null && $user->province_id !== ''
                    ? (string) $user->province_id
                    : null;
                $oldCityId = $user->city_id !== null && $user->city_id !== ''
                    ? (string) $user->city_id
                    : null;

                if ($oldCityId !== null) {
                    $oldRegency = $existingRegencies->get($oldCityId);

                    if (!$oldRegency) {
                        throw new RuntimeException(
                            "Profil pengguna ID {$user->id} memiliki city_id {$oldCityId} yang tidak ditemukan. Migrasi wilayah dibatalkan."
                        );
                    }

                    if (
                        $oldProvinceId !== null
                        && (string) $oldRegency->province_id !== $oldProvinceId
                    ) {
                        throw new RuntimeException(
                            "Provinsi dan kabupaten/kota pada profil pengguna ID {$user->id} tidak cocok. Migrasi wilayah dibatalkan."
                        );
                    }

                    $matches = $newRegenciesByName[$this->normalizeName((string) $oldRegency->name)] ?? [];

                    if (count($matches) !== 1) {
                        throw new RuntimeException(
                            "Kabupaten/kota '{$oldRegency->name}' pada profil pengguna ID {$user->id} tidak dapat dipetakan secara unik. Tidak ada perubahan database yang dilakukan."
                        );
                    }

                    $newRegency = $matches[0];
                    $changes = [];

                    if ($oldCityId !== $newRegency['id']) {
                        $changes['city_id'] = $newRegency['id'];
                    }

                    if ($oldProvinceId !== $newRegency['province_id']) {
                        $changes['province_id'] = $newRegency['province_id'];
                    }

                    if ($changes !== []) {
                        $userUpdates[$user->id] = $changes;
                    }

                    continue;
                }

                if ($oldProvinceId !== null) {
                    $oldProvince = $existingProvinces->get($oldProvinceId);

                    if (!$oldProvince) {
                        throw new RuntimeException(
                            "Profil pengguna ID {$user->id} memiliki province_id {$oldProvinceId} yang tidak ditemukan. Migrasi wilayah dibatalkan."
                        );
                    }

                    $matches = $newProvincesByName[$this->normalizeName((string) $oldProvince->name)] ?? [];

                    if (count($matches) !== 1) {
                        throw new RuntimeException(
                            "Provinsi '{$oldProvince->name}' pada profil pengguna ID {$user->id} tidak dapat dipetakan secara unik. Tidak ada perubahan database yang dilakukan."
                        );
                    }

                    $newProvinceId = $matches[0]['id'];

                    if ($oldProvinceId !== $newProvinceId) {
                        $userUpdates[$user->id] = ['province_id' => $newProvinceId];
                    }
                }
            }

            DB::table('provinces')->upsert(
                $provinceRows,
                ['id'],
                ['name']
            );

            DB::table('regencies')->upsert(
                $regencyRows,
                ['id'],
                ['province_id', 'name']
            );

            foreach ($userUpdates as $userId => $changes) {
                DB::table('users')->where('id', $userId)->update($changes);
            }

            // Remove obsolete legacy codes only after all user references
            // have been validated and remapped inside this transaction.
            $removedRegencies = DB::table('regencies')
                ->whereNotIn('id', $regencyIds)
                ->count();

            DB::table('regencies')
                ->whereNotIn('id', $regencyIds)
                ->delete();

            $removedProvinces = DB::table('provinces')
                ->whereNotIn('id', $provinceIds)
                ->count();

            DB::table('provinces')
                ->whereNotIn('id', $provinceIds)
                ->delete();

            $orphanCount = DB::table('regencies as r')
                ->leftJoin('provinces as p', 'p.id', '=', 'r.province_id')
                ->whereNull('p.id')
                ->count();

            if ($orphanCount !== 0) {
                throw new RuntimeException(
                    "Ditemukan {$orphanCount} kabupaten/kota tanpa provinsi induk. Transaksi database dibatalkan."
                );
            }

            if (
                DB::table('provinces')->count() !== count($provinceRows)
                || DB::table('regencies')->count() !== count($regencyRows)
            ) {
                throw new RuntimeException(
                    'Jumlah akhir data wilayah tidak sesuai dengan sumber. Transaksi database dibatalkan.'
                );
            }

            return [
                'updated_users' => count($userUpdates),
                'removed_regencies' => $removedRegencies,
                'removed_provinces' => $removedProvinces,
            ];
        });

        $this->command->info('Data wilayah Indonesia berhasil diperbarui.');
        $this->command->info("Provinsi: {$expectedProvinceCount}");
        $this->command->info("Kabupaten/kota: {$expectedRegencyCount}");
        $this->command->info("Profil pengguna yang disesuaikan: {$result['updated_users']}");
        $this->command->info("Kode kabupaten/kota lama yang dihapus: {$result['removed_regencies']}");
        $this->command->info("Kode provinsi lama yang dihapus: {$result['removed_provinces']}");
    }

    /**
     * Fetch and validate the API v2 response wrapper.
     */
    private function fetchData(string $url): array
    {
        $response = Http::acceptJson()
            ->timeout(30)
            ->retry(3, 500)
            ->get($url);

        if (!$response->successful()) {
            throw new RuntimeException(
                "Gagal mengambil data wilayah. HTTP {$response->status()} dari {$url}."
            );
        }

        $payload = $response->json();

        if (!is_array($payload) || !array_key_exists('data', $payload) || !is_array($payload['data'])) {
            throw new RuntimeException("Format respons API wilayah tidak valid: {$url}.");
        }

        return $payload['data'];
    }

    private function normalizeName(string $name): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($name));

        return Str::upper($normalized ?? trim($name));
    }
}
