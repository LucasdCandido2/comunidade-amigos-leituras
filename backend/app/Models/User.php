<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'reputation',
        'topics_count',
        'interactions_count',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'reputation' => 'integer',
        'topics_count' => 'integer',
        'interactions_count' => 'integer',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function permissions()
    {
        return $this->roles()->with('permissions')->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->unique();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->contains($permission);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    public function works()
    {
        return $this->hasMany(Work::class);
    }

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    public function addReputation(int $points): void
    {
        $this->increment('reputation', $points);
    }

    public function getReputationLevel(): string
    {
        $rep = $this->reputation ?? 0;
        return match (true) {
            $rep >= 1000 => 'Lenda 📚',
            $rep >= 500 => 'Veterano 🎖️',
            $rep >= 200 => 'Expert 🏅',
            $rep >= 100 => 'Avançado ⭐',
            $rep >= 50 => 'Intermediário 🎯',
            $rep >= 10 => 'Iniciante 🌱',
            default => 'Novato',
        };
    }
}
