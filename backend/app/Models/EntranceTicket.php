<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntranceTicket extends Model
{
    protected $fillable = [
        'tour_id', 'tour_destination_id', 'attraction_name', 'city',
        'ticket_type', 'visit_date', 'visit_time',
        'pax_count', 'price_per_person_usd', 'total_price_usd',
        'booking_status', 'supplier_name', 'supplier_contact', 'notes',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'price_per_person_usd' => 'decimal:2',
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
}
