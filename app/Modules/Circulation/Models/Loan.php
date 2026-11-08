<?php

namespace Modules\Circulation\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Circulation\ValueObjects\Denda;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_BORROWED = 'borrowed';

    public const STATUS_RETURNED = 'returned';

    protected $fillable = [
        'member_id',
        'member_name',
        'loan_number',
        'borrowed_at',
        'due_at',
        'returned_at',
        'status',
        'total_fine',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
            'total_fine' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi dalam Sales/Circulation Context
    |--------------------------------------------------------------------------
    */

    /**
     * HasMany: Peminjaman -> DetailPeminjaman.
     */
    public function items(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(LoanStatusHistory::class);
    }

    /**
     * Referensi lintas context (read-only) ke Membership.
     * Untuk penulisan data anggota, gunakan MemberServiceInterface.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(\Modules\Membership\Models\Member::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_BORROWED)
            ->where('due_at', '<', now());
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('borrowed_at', '>=', now()->subDays($days));
    }

    public function scopeForMember(Builder $query, int $memberId): Builder
    {
        return $query->where('member_id', $memberId);
    }

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('loan_number', 'like', "%{$keyword}%")
                ->orWhere('member_name', 'like', "%{$keyword}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Domain Behaviour
    |--------------------------------------------------------------------------
    */

    public function isReturned(): bool
    {
        return $this->status === self::STATUS_RETURNED;
    }

    public function isOverdue(): bool
    {
        return ! $this->isReturned() && $this->due_at !== null && $this->due_at->isPast();
    }

    public function daysLate(): int
    {
        if ($this->returned_at === null || $this->due_at === null) {
            return 0;
        }

        return Denda::between($this->due_at, $this->returned_at)->getDaysLate();
    }

    public function calculateFine(): Denda
    {
        if ($this->returned_at === null || $this->due_at === null) {
            return Denda::fromDaysLate(0);
        }

        return Denda::between($this->due_at, $this->returned_at);
    }

    public function totalItems(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
