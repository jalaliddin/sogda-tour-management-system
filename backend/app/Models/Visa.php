<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visa extends Model
{
    protected $fillable = [
        'tour_id', 'visa_type', 'country_from', 'country_to',
        'applicant_name', 'passport_number', 'passport_expiry', 'nationality',
        'application_date', 'submission_date', 'expected_date', 'issued_date',
        'visa_number', 'cost_usd', 'paid_amount', 'status',
        'rejection_reason', 'notes', 'processed_by',
    ];

    protected $casts = [
        'passport_expiry' => 'date',
        'application_date' => 'date',
        'submission_date' => 'date',
        'expected_date' => 'date',
        'issued_date' => 'date',
        'cost_usd' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
