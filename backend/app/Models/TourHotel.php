<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourHotel extends Model
{
    protected $fillable = [
        'tour_id', 'tour_destination_id', 'hotel_id',
        'check_in_date', 'check_in_time', 'check_out_date', 'check_out_time',
        'room_type', 'room_count', 'pax_per_room',
        'price_per_night_usd', 'total_price_usd',
        'status', 'hotel_confirmation_number', 'notes',
        'booking_date', 'confirmed_by', 'confirmed_at',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'booking_date' => 'date',
        'confirmed_at' => 'datetime',
        'price_per_night_usd' => 'decimal:2',
        'total_price_usd' => 'decimal:2',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function destination()
    {
        return $this->belongsTo(TourDestination::class, 'tour_destination_id');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
