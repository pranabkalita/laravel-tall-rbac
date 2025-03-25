<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'pmid',
        'title',
        'published_on',
        'revised_on',
        'success'
    ];

    protected $casts = [
        'published_on' => 'date',
        'last_revised_on' => 'date'
    ];

    // Methods

    // Relations
    public function protein()
    {
        return $this->belongsTo(Protein::class);
    }

    public function mutations()
    {
        return $this->hasMany(Mutation::class);
    }
}
