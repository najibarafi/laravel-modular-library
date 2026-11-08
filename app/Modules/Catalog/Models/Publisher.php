<?php

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publisher extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'address', 'phone', 'email'];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
