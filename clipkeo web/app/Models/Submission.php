<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'bounty_id',
        'user_id',
        'clipper_id',
        'linked_account_id',
        'video_url',
        'video_id',
        'status',             // pending, approved, rejected
        'payout_status',      // [BARU] unpaid, paid, partial
        'rejection_reason',
        'verification_code',
        'caption_content',
        'analytics_path',
        'prize_amount',       // Total uang yang sudah didapat dari video ini
        'rank',
        'views_count',        // Views saat ini (Realtime/Updated)
        'last_paid_views',    // [BARU] Views terakhir yang sudah dicairkan (Checkpoint)
        'approved_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class, 'clipper_id');
    }

    public function bounty()
    {
        return $this->belongsTo(Bounty::class);
    }

    public function clipper()
    {
        return $this->belongsTo(User::class, 'clipper_id');
    }
    public function linkedAccount()
    {
        return $this->belongsTo(LinkedAccount::class, 'linked_account_id');
    }
}