<?php

namespace Modules\Membership\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class)
            ->withPivot('assigned_at')
            ->withTimestamps();
    }
}
