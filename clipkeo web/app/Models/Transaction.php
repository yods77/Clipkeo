<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];

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
        'paid_at'
    ];

    public $timestamps = false;

    // Opsional: Agar Laravel tahu kolom created_at tetap ada (diurus otomatis oleh Database)
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
}