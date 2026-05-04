<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'counterparty_id', 'offer_type', 'offer_name', 'destination_countries',
        'start_date', 'end_date', 'pax_min', 'pax_max',
        'price_per_person_usd', 'total_price_usd', 'currency',
        'includes', 'excludes', 'itinerary', 'validity_date',
        'status', 'notes', 'received_date', 'reviewed_by',
    ];

    protected $casts = [
        'destination_countries' => 'array',
        'includes' => 'array',
        'excludes' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'validity_date' => 'date',
        'received_date' => 'date',
        'price_per_person_usd' => 'decimal:2',
        'total_price_usd' => 'decimal:2',
    ];

    public function counterparty()
    {
        return $this->belongsTo(Counterparty::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
