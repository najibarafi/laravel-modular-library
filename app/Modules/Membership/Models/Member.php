<?php

namespace Modules\Membership\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_BORROWING = 'borrowing';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'membership_number',
        'name',
        'email',
        'phone',
        'address',
        'status',
        'max_loans',
        'active_loans',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'max_loans' => 'integer',
            'active_loans' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */

    /**
     * Keanggotaan (riwayat membership).
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * Many-to-Many Member <-> Role dengan pivot assigned_at.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Anggota yang boleh meminjam: status aktif/borrowing dan belum mencapai batas.
     */
    public function scopeEligible(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_BORROWING])
            ->whereColumn('active_loans', '<', 'max_loans');
    }

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('membership_number', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Domain Behaviour
    |--------------------------------------------------------------------------
    */

    public function isEligible(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_BORROWING], true)
            && $this->active_loans < $this->max_loans;
    }

    public function hasReachedLoanLimit(): bool
    {
        return $this->active_loans >= $this->max_loans;
    }

    public function activeMembership(): ?Membership
    {
        return $this->memberships()
            ->where('status', 'active')
            ->orderByDesc('start_date')
            ->first();
    }
}
