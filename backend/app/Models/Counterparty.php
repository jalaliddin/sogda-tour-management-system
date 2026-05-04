<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Counterparty extends Model
{
    protected $fillable = [
        'company_name', 'type', 'country', 'city', 'address',
        'contact_person', 'phone', 'email', 'website',
        'contract_number', 'contract_date', 'contract_expiry',
        'commission_percent', 'payment_terms', 'currency',
        'bank_details', 'notes', 'rating', 'is_active', 'documents', 'created_by',
    ];

    protected $casts = [
        'bank_details' => 'array',
        'documents' => 'array',
        'is_active' => 'boolean',
        'contract_date' => 'date',
        'contract_expiry' => 'date',
        'commission_percent' => 'decimal:2',
        'rating' => 'integer',
    ];

    public function tours()
    {
        return $this->hasMany(Tour::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function hotels()
    {
        return $this->hasMany(Hotel::class);
    }

    public function transports()
    {
        return $this->hasMany(Transport::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
