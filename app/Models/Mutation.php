<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mutation extends Model
{
    protected $fillable = [
        'name'
    ];


    // Scopes

    public function scopeFilterBySearch($query, $search)
    {
        if ($search) {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }
    }

    public function scopeSortBy($query, $sortBy, $sortDirection)
    {
        if (in_array($sortBy, ['name'])) {
            $query->orderBy($sortBy, $sortDirection);
        }
    }

    public function scopeUniqueByName($query)
    {
        return $query->unique('name');
    }


    // Relations

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
