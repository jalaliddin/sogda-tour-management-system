<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transport extends Model
{
    protected $fillable = [
        'type', 'brand', 'model', 'plate_number', 'capacity',
        'driver_name', 'driver_phone', 'driver_license',
        'is_own', 'counterparty_id', 'status', 'notes', 'is_active',
    ];

    protected $casts = [
        'is_own' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function counterparty()
    {
        return $this->belongsTo(Counterparty::class);
    }

    public function tourTransports()
    {
        return $this->hasMany(TourTransport::class);
    }
}
