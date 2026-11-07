<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isPustakawan(): bool
    {
        return $this->role === 'pustakawan';
    }

    public function isKepala(): bool
    {
        return $this->role === 'kepala_perpustakaan';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['pustakawan', 'kepala_perpustakaan']);
    }

    public function isMember(): bool
    {
        return in_array($this->role, ['siswa', 'guru']);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'pustakawan' => 'Pustakawan',
            'kepala_perpustakaan' => 'Kepala Perpustakaan',
            'guru' => 'Guru',
            'siswa' => 'Siswa',
            default => ucfirst($this->role),
        };
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function activeLoans(): HasMany
    {
        return $this->hasMany(Loan::class)->where('status', 'dipinjam');
    }

    public function borrowRequests(): HasMany
    {
        return $this->hasMany(BorrowRequest::class);
    }

    public function bookSuggestions(): HasMany
    {
        return $this->hasMany(BookSuggestion::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(BookReturn::class);
    }
}
