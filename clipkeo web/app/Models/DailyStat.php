<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyStat extends Model
{
    use HasFactory;

    // === TAMBAHKAN BAGIAN INI ===
    protected $fillable = [
        'submission_id',
        'date',
        'views_count'
    ];
}
