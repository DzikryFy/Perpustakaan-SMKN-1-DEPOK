<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookReturn extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'loan_id',
        'book_id',
        'user_id',
        'petugas_id',
        'tanggal_kembali',
        'kondisi_buku',
        'denda_keterlambatan',
        'denda_kerusakan',
        'total_denda',
        'status_pembayaran_denda',
        'catatan_petugas',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kembali' => 'date',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
