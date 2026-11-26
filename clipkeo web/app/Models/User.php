<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'fcm_token',
        'username',
        'phone_number',
        'role',
        'wallet_balance',
        'reputation_score',
        'avatar_url',
        'is_verified',
        'google_id',
        'security_pin',
        'gender',
        'birthday',
        'province_id',
        'city_id',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'security_pin',
        'google_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function linkedAccounts()
    {
        return $this->hasMany(LinkedAccount::class);
    }

    public function bounties()
    {
        return $this->hasMany(Bounty::class, 'publisher_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'clipper_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function payoutMethods()
    {
        return $this->hasMany(PayoutMethod::class);
    }

    public function payoutMethod()
    {
        return $this->hasOne(PayoutMethod::class)->where('is_primary', true);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges', 'user_id', 'badge_id')
            ->withPivot('awarded_at');
    }

    public function getCurrentBadge()
    {
        return $this->badges()->orderBy('xp_req', 'desc')->first();
    }

    public function addReputation($amount)
    {
        $this->increment('reputation_points', $amount);
        $this->updateTierLevel();
        $this->checkAndAwardBadges();
    }

    private function updateTierLevel()
    {
        $points = $this->reputation_points;

        if ($points >= 5000) {
            $level = 4;
        } elseif ($points >= 2000) {
            $level = 3;
        } elseif ($points >= 500) {
            $level = 2;
        } else {
            $level = 1;
        }

        if ($this->reputation_score !== $level) {
            $this->update(['reputation_score' => $level]);
        }
    }

    public function getTierNameAttribute()
    {
        return match ($this->reputation_score) {
            1 => 'Bronze Clipper',
            2 => 'Silver Clipper',
            3 => 'Gold Clipper',
            4 => 'Diamond Clipper',
            default => 'Bronze Clipper',
        };
    }

    public function getReputationProgress()
    {
        $points = $this->reputation_points;

        if ($points < 500) {
            $target = 500;
        } elseif ($points < 2000) {
            $target = 2000;
        } elseif ($points < 5000) {
            $target = 5000;
        } else {
            return 100;
        }

        return round(($points / $target) * 100);
    }

    private function checkAndAwardBadges()
    {
        $newBadges = Badge::where('xp_req', '<=', $this->reputation_points)
            ->whereDoesntHave('users', function ($query) {
                $query->where('user_id', $this->id);
            })
            ->get();

        foreach ($newBadges as $badge) {
            $this->badges()->attach($badge->id, ['awarded_at' => now()]);
        }
    }
}
