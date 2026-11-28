<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAWAL = 'withdrawal';
    public const TYPE_BOUNTY_HOLD = 'bounty_hold';
    public const TYPE_BOUNTY_PRIZE = 'bounty_prize';
    public const TYPE_BOUNTY_REFUND = 'bounty_refund';
    public const TYPE_PLATFORM_FEE = 'platform_fee';
    public const TYPE_WITHDRAWAL_REFUND = 'withdrawal_refund';

    /**
     * Temporary compatibility aliases for older controller call sites.
     * Persist only values supported by the transactions.type ENUM.
     */
    private const LEGACY_TYPE_MAP = [
        'reward' => self::TYPE_BOUNTY_PRIZE,
        'payout' => self::TYPE_BOUNTY_PRIZE,
        'refund' => self::TYPE_BOUNTY_REFUND,
    ];

    protected $guarded = [];

    public function setTypeAttribute($value): void
    {
        $this->attributes['type'] = is_string($value)
            ? (self::LEGACY_TYPE_MAP[$value] ?? $value)
            : $value;
    }

    public function withdrawalRequest()
    {
        return $this->belongsTo(WithdrawalRequest::class, 'withdrawal_request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'description',
        'status',
        'reference_id',
        'currency',
        'payment_url',
        'external_ref',
        'bank_code',
        'account_number',
        'account_holder',
        'payment_channel',
        'paid_at',
        'withdrawal_request_id'
    ];

    public $timestamps = false;

    // Opsional: Agar Laravel tahu kolom created_at tetap ada (diurus otomatis oleh Database)
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
}