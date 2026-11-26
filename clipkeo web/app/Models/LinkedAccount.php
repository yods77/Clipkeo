<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkedAccount extends Model
{
    use HasFactory;

    // Tambahkan baris ini agar bisa di-input via Controller
    protected $fillable = [
        'user_id',
        'provider_name',
        'provider_id',
        'username',
        'profile_url',
        'verified_at'
    ];

    // Relasi balik ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}