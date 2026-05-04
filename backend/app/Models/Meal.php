<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    protected $fillable = [
        'tour_id', 'tour_destination_id', 'meal_type', 'meal_date', 'meal_time',
        'restaurant_id', 'menu_type', 'menu_description',
        'pax_count', 'price_per_person_usd', 'total_price_usd', 'status', 'notes',
    ];

    protected $casts = [
        'meal_date' => 'date',
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

    public function restaurant()
    {
        return $this->belongsTo(Counterparty::class, 'restaurant_id');
    }
}
