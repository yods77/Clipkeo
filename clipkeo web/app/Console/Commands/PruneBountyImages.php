<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bounty;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PruneBountyImages extends Command
{
    // Nama perintah terminal
    protected $signature = 'bounty:prune-images';
    protected $description = 'Hapus gambar cover misi yang sudah lama selesai untuk hemat storage';

    public function handle()
    {
        // Cari misi yang statusnya 'completed' DAN sudah selesai lebih dari 60 hari yang lalu
        // Serta masih punya gambar (image_path tidak null)
        $oldBounties = Bounty::where('status', 'completed')
            ->where('updated_at', '<', Carbon::now()->subDays(30))
            ->whereNotNull('image_path')
            ->get();

        $count = 0;

        foreach ($oldBounties as $bounty) {
            // Hapus file fisik di folder storage
            if (Storage::disk('public')->exists($bounty->image_path)) {
                Storage::disk('public')->delete($bounty->image_path);
            }

            // Update database: set image_path jadi NULL
            // (Data misi tetap ada untuk arsip, tapi gambarnya hilang)
            $bounty->update(['image_path' => null]);
            $count++;
        }

        $this->info("Berhasil membersihkan $count gambar cover dari misi lama!");
    }
}
