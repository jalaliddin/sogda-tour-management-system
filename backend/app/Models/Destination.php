<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $fillable = [
        'name', 'country', 'country_code', 'region', 'type',
        'airport_code', 'timezone', 'description', 'image',
        'attractions', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'attractions' => 'array',
        'is_active' => 'boolean',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->name}, {$this->country}";
    }
}
