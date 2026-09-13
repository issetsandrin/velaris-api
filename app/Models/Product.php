<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'collection',
        'family',
        'tagline',
        'description',
        'notes_top',
        'notes_heart',
        'notes_base',
        'wax',
        'featured',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class)->orderBy('position');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
