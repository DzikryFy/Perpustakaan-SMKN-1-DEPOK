<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',
        'borrow_request_id',
        'tanggal_pinjam',
        'batas_kembali',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'batas_kembali' => 'date',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function borrowRequest(): BelongsTo
    {
        return $this->belongsTo(BorrowRequest::class);
    }

    public function bookReturn(): HasOne
    {
        return $this->hasOne(BookReturn::class);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'dipinjam' && Carbon::today()->gt($this->batas_kembali);
    }

    public function getOverdueDaysAttribute(): int
    {
        if (!$this->is_overdue) {
            return 0;
        }
        return (int) Carbon::today()->diffInDays($this->batas_kembali);
    }

    public function getEstimatedFineAttribute(): int
    {
        return $this->overdue_days * 1000;
    }
}
