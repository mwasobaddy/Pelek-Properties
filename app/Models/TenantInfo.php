<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantInfo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_id',
        'tenant_name',
        'tenant_phone',
        'tenant_email',
        'lease_start',
        'lease_end',
        'monthly_rent',
        'security_deposit',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'lease_start' => 'date',
        'lease_end' => 'date',
        'monthly_rent' => 'decimal:2',
        'security_deposit' => 'decimal:2',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
