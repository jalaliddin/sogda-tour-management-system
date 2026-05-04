<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourDestination extends Model
{
    protected $fillable = [
        'tour_id', 'city', 'custom_city_name', 'arrival_date', 'departure_date',
        'day_number', 'nights_count', 'order_index', 'notes',
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'departure_date' => 'date',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function hotels()
    {
        return $this->hasMany(TourHotel::class);
    }

    public function transports()
    {
        return $this->hasMany(TourTransport::class);
    }

    public function meals()
    {
        return $this->hasMany(Meal::class);
    }

    public function entranceTickets()
    {
        return $this->hasMany(EntranceTicket::class);
    }
}
