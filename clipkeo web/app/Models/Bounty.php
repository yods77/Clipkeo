<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bounty extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | CONFIGURATION
    |--------------------------------------------------------------------------
    */
    protected $guarded = ['id'];

    protected $fillable = [
        'publisher_id',
        'title',
        'category',
        'slug',
        'description',
        'image_path',
        'source_link',
        'reward_amount',         // Sekarang berfungsi sebagai "Total Max Budget"
        'min_views',             // Syarat minimal views untuk claim
        'rate_per_view',         // [BARU] Harga per view (untuk CPM)
        'last_viewed_at',
        'currency',
        'platform',
        'winner_count',
        'distribution_strategy', // Hanya: 'performance_tiered' atau 'cpm'
        'required_hashtags',
        'required_mentions',
        'deadline_at',
        'status',
        'rejection_reason',
        'remaining_budget',
        'rules',                 // [PENTING] Menyimpan JSON Tier
    ];

    protected $casts = [
        'attachments' => 'array',
        'rules' => 'array', // Auto-convert JSON ke Array PHP
        'deadline_at' => 'datetime',
        'last_viewed_at' => 'datetime',
        'platform' => 'array',
        'winner_count' => 'integer',
        'distribution_strategy' => 'string',
        'rate_per_view' => 'decimal:2',
        'remaining_budget' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function publisher()
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper untuk menghitung notifikasi badge
    public function unreadSubmissionsCount()
    {
        $lastViewed = $this->last_viewed_at;
        if (!$lastViewed) {
            return $this->submissions()->count();
        }
        return $this->submissions()
            ->where('created_at', '>', $lastViewed)
            ->count();
    }
    public function getActualMinViewsAttribute()
    {
        // 1. Jika Strategi CPM
        if ($this->distribution_strategy === 'cpm') {
            return (int) ($this->min_views ?? 0);
        }

        // 2. Jika Strategi Performance Tiered
        if ($this->distribution_strategy === 'performance_tiered' && isset($this->rules['tiers'])) {
            // Kita ambil semua 'target_views' lalu cari yang paling kecil
            $minViews = collect($this->rules['tiers'])->min('target_views');
            return (int) ($minViews ?? 0);
        }

        return 0;
    }
}