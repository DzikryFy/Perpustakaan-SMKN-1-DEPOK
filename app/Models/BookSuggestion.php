<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'judul',
        'penulis',
        'penerbit',
        'alasan',
        'tanggal_saran',
        'status',
        'catatan_review',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_saran' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'baru' => ['label' => 'Baru', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
            'ditinjau' => ['label' => 'Ditinjau', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
            'disetujui' => ['label' => 'Disetujui', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'ditolak' => ['label' => 'Ditolak', 'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
            default => ['label' => ucfirst($this->status), 'class' => 'bg-slate-50 text-slate-700 border-slate-200'],
        };
    }
}
