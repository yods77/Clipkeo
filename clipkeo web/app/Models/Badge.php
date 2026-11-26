<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    // Sesuai dengan kolom tabel badges Bosku
    protected $fillable = [
        'name',
        'icon_url',
        'xp_req', // Syarat XP
    ];

    // Relasi ke User (melalui tabel pivot user_badges)
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges', 'badge_id', 'user_id')
                    ->withPivot('awarded_at'); // Ambil kolom tanggal dapat juga
    }
}