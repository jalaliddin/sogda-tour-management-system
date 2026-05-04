<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $fillable = [
        'counterparty_id', 'branch_id', 'name', 'city', 'address',
        'stars', 'category', 'contact_person', 'phone', 'email',
        'check_in_time', 'check_out_time', 'room_types', 'amenities',
        'notes', 'is_own', 'is_active',
    ];

    protected $casts = [
        'room_types' => 'array',
        'amenities' => 'array',
        'is_own' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function counterparty()
    {
        return $this->belongsTo(Counterparty::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function bookings()
    {
        return $this->hasMany(TourHotel::class);
    }
}
