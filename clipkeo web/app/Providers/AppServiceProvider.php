<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Submission;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
        
        // Kita hitung jumlahnya
        $monitorCount = Submission::where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes(5)) // Sesuaikan waktu (5 menit/12 jam)
            ->count();

        // Kirim variabel $monitorCount ke semua file blade
        $view->with('monitorCount', $monitorCount);
    });
    }
    
}
