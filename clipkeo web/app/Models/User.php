<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'fcm_token',
        'username',       // Wajib
        'phone_number',
        'role',           // Wajib
        'wallet_balance', // Baru
        'reputation_score', // Baru
        'avatar_url',     // Baru
        'is_verified',    // Baru
        'google_id',
        'security_pin',
        'gender',
        'birthday',
        'province_id',
        'city_id',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'security_pin',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relasi 1: User punya banyak Akun Sosmed
    public function linkedAccounts()
    {
        return $this->hasMany(LinkedAccount::class);
    }

    // Relasi 2: User (sebagai Publisher) punya banyak Sayembara
    public function bounties()
    {
        return $this->hasMany(Bounty::class, 'publisher_id');
    }

    // Relasi 3: User (sebagai Clipper) punya banyak Setoran Video
    public function submissions()
    {
        return $this->hasMany(Submission::class, 'clipper_id');
    }

    // Relasi 4: User punya Dompet (Transaksi)
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function payoutMethods()
    {
        return $this->hasMany(PayoutMethod::class);
    }



    // Relasi ke tabel payout_methods
    public function payoutMethod()
    {
        return $this->hasOne(PayoutMethod::class)->where('is_primary', true);
    }

    // ==========================================
    // SISTEM REPUTASI & BADGES
    // ==========================================

    // 1. Relasi ke Badges
    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges', 'user_id', 'badge_id')
            ->withPivot('awarded_at');
    }

    // 2. Fungsi Ambil Badge Tertinggi (Untuk dipasang di Card Profil)
    public function getCurrentBadge()
    {
        // Ambil badge yang dimiliki user, urutkan dari syarat XP tertinggi
        return $this->badges()->orderBy('xp_req', 'desc')->first();
    }

    // 3. MESIN OTOMATIS: Tambah Poin & Cek Hadiah
    // Panggil fungsi ini setiap User selesaikan misi!
    public function addReputation($amount)
    {
        // Tambah Poin ke kolom reputation_points
        $this->increment('reputation_points', $amount);

        // Update Nama Tier di kolom reputation_score (Opsional/Sesuai kebutuhan)
        $this->updateTierLevel();

        // Cek apakah berhak dapat Badge baru?
        $this->checkAndAwardBadges();
    }

    // 4. Logika Update Nama Tier (reputation_score)
    private function updateTierLevel()
    {
        $points = $this->reputation_points;
        
        // Cek Poin & Tentukan Level Angka
        if ($points >= 5000) {
            $level = 4; // Diamond
        } elseif ($points >= 2000) {
            $level = 3; // Gold
        } elseif ($points >= 500) {
            $level = 2; // Silver
        } else {
            $level = 1; // Bronze (0 - 499 XP)
        }

        // Simpan jika level berubah
        if ($this->reputation_score !== $level) {
            $this->update(['reputation_score' => $level]);
        }
    }

    public function getTierNameAttribute()
    {
        return match ($this->reputation_score) {
            1 => 'Bronze Clipper',   // Level Awal
            2 => 'Silver Clipper',
            3 => 'Gold Clipper',
            4 => 'Diamond Clipper',  // Level Tertinggi
            default => 'Bronze Clipper',
        };
    }

    public function getReputationProgress()
    {
        $points = $this->reputation_points;

        // Target Level Berikutnya
        if ($points < 500) {
            $target = 500;  // Bronze menuju Silver
        } elseif ($points < 2000) {
            $target = 2000; // Silver menuju Gold
        } elseif ($points < 5000) {
            $target = 5000; // Gold menuju Diamond
        } else {
            return 100; // Sudah Diamond (Mentok)
        }

        // Rumus Persentase: (Poin Saat Ini / Target) * 100
        $percentage = ($points / $target) * 100;

        // Kita bulatkan agar angkanya rapi (tidak koma-koma)
        return round($percentage);
    }
    // 5. Logika Cek & Beri Badge
    private function checkAndAwardBadges()
    {
        // Cari badge yang syarat XP-nya sudah terlampaui TAPI belum dimiliki user
        $newBadges = Badge::where('xp_req', '<=', $this->reputation_points)
            ->whereDoesntHave('users', function ($query) {
                $query->where('user_id', $this->id);
            })
            ->get();

        // Berikan semua badge yang memenuhi syarat
        foreach ($newBadges as $badge) {
            $this->badges()->attach($badge->id, ['awarded_at' => now()]);
        }
    }
}
