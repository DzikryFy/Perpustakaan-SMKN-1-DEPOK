<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_bos',
        'judul',
        'jenis_barang',
        'kategori',
        'penerbit',
        'stok',
        'tahun_cetak',
        'asal_perolehan',
        'gambar',
        'deskripsi',
    ];

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function borrowRequests(): HasMany
    {
        return $this->hasMany(BorrowRequest::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(BookReturn::class);
    }

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->gambar && Storage::disk('public')->exists('books/' . $this->gambar)) {
            return asset('storage/books/' . $this->gambar);
        }
        if ($this->gambar && file_exists(public_path('images/buku/' . $this->gambar))) {
            return asset('images/buku/' . $this->gambar);
        }
        return null;
    }

    public function getCategoryColorAttribute(): string
    {
        return match (strtolower($this->kategori)) {
            'umum' => 'blue',
            'motivasi' => 'amber',
            'sastra' => 'purple',
            'religi' => 'emerald',
            'kejuruan' => 'indigo',
            default => 'slate',
        };
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('judul', 'like', "%{$term}%")
              ->orWhere('penerbit', 'like', "%{$term}%")
              ->orWhere('no_bos', 'like', "%{$term}%");
        });
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        if (!$category || $category === 'all') {
            return $query;
        }

        return $query->where('kategori', $category);
    }
}
