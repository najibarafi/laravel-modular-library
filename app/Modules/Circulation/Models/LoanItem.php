<?php

namespace Modules\Circulation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'book_id',
        'book_title',
        'book_isbn',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Sengaja TIDAK ada relasi book().
     *
     * DetailPeminjaman menyimpan snapshot (judul & ISBN) agar riwayat
     * tidak berubah ketika data buku di Catalog berubah/dihapus.
     */
}
