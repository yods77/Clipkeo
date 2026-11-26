<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\PruneAnalyticsProof;
use App\Console\Commands\PruneBountyImages;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('analytics:prune', function () {
    $this->call('analytics:prune');
})->purpose('Hapus video bukti lama untuk hemat storage');

Artisan::command('bounty:prune-images', function () {
    $this->call('bounty:prune-images');
})->purpose('Hapus gambar cover misi yang sudah lama selesai untuk hemat storage');
    