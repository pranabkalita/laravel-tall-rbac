<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Protein extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name'
    ];

    // Relations
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function mutations(): HasManyThrough
    {
        return $this->hasManyThrough(Mutation::class, Article::class);
    }
}
