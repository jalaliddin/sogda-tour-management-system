<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourTransport extends Model
{
    protected $fillable = [
        'tour_id', 'tour_destination_id', 'transport_id', 'counterparty_id',
        'route_from', 'route_to', 'transport_date', 'departure_time', 'arrival_time',
        'transport_type', 'vehicle_description', 'price_usd', 'is_own_fleet',
        'driver_name', 'driver_phone', 'notes', 'status',
    ];

    protected $casts = [
        'transport_date' => 'date',
        'is_own_fleet' => 'boolean',
        'price_usd' => 'decimal:2',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function destination()
    {
        return $this->belongsTo(TourDestination::class, 'tour_destination_id');
    }

    public function transport()
    {
        return $this->belongsTo(Transport::class);
    }

    public function counterparty()
    {
        return $this->belongsTo(Counterparty::class);
    }
}
