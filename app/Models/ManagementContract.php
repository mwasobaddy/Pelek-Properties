<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagementContract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'admin_id',
        'contract_type',
        'management_fee_percentage',
        'base_fee',
        'start_date',
        'end_date',
        'payment_schedule',
        'services_included',
        'special_terms',
        'status',
    ];

    protected $casts = [
        'services_included' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'management_fee_percentage' => 'decimal:2',
        'base_fee' => 'decimal:2',
    ];
    
    /**
     * Get the services included attribute.
     *
     * @param  mixed  $value
     * @return array
     */
    public function getServicesIncludedAttribute($value)
    {
        // Always ensure this returns an array, even if null
        if (is_null($value)) {
            return [];
        }
        
        // If it's already an array, return it
        if (is_array($value)) {
            return $value;
        }
        
        // If it's a JSON string, decode it
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        // Default fallback
        return [];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->start_date->isPast() && 
               $this->end_date->isFuture();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }
}
