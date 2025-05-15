<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialLease extends Model
{
    protected $fillable = [
        'property_id',
        'tenant_name',
        'tenant_business',
        'tenant_contact',
        'start_date',
        'end_date',
        'monthly_rate',
        'security_deposit',
        'lease_type',
        'terms_conditions',
        'duration_months',
        'status',
        'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_rate' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'terms_conditions' => 'json',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
