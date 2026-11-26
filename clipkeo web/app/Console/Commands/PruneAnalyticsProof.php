<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Submission;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PruneAnalyticsProof extends Command
{
    // Nama perintah yang akan dijalankan
    protected $signature = 'analytics:prune';
    protected $description = 'Hapus video bukti lama untuk hemat storage';

    public function handle()
    {
        // Cari submission yang sudah selesai (Approved/Rejected) DAN sudah lebih dari 30 hari
        $oldSubmissions = Submission::whereIn('status', ['approved', 'rejected'])
            ->where('updated_at', '<', Carbon::now()->subDays(30)) // Umur > 30 hari
            ->whereNotNull('analytics_path')
            ->get();

        $count = 0;

        foreach ($oldSubmissions as $sub) {
            // Hapus file fisik di storage
            if (Storage::disk('public')->exists($sub->analytics_path)) {
                Storage::disk('public')->delete($sub->analytics_path);
            }

            // Hapus path di database (biar admin tau file sudah expired)
            $sub->update(['analytics_path' => null]);
            $count++;
        }

        $this->info("Berhasil membersihkan $count video bukti lama!");
    }
}
