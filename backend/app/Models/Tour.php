<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tour extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_name', 'tour_code', 'country', 'counterparty_id', 'assigned_staff_id',
        'start_date', 'end_date', 'duration_days', 'pax_count', 'pax_adults', 'pax_children',
        'arrival_type', 'arrival_flight_number', 'arrival_flight_time', 'arrival_terminal',
        'departure_type', 'departure_flight_number', 'departure_flight_time',
        'status', 'total_price_usd', 'total_price_uzs', 'currency',
        'notes', 'special_requests', 'created_by', 'confirmed_by', 'confirmed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'confirmed_at' => 'datetime',
        'total_price_usd' => 'decimal:2',
        'total_price_uzs' => 'decimal:2',
    ];

    public function counterparty()
    {
        return $this->belongsTo(Counterparty::class);
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function destinations()
    {
        return $this->hasMany(TourDestination::class)->orderBy('order_index');
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

    public function visas()
    {
        return $this->hasMany(Visa::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
