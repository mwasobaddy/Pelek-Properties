<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyOffer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'admin_id',
        'client_name',
        'client_phone',
        'client_email',
        'offer_amount',
        'payment_method',
        'terms_conditions',
        'status',
        'valid_until',
        'notes',
    ];

    protected $casts = [
        'offer_amount' => 'decimal:2',
        'valid_until' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            });
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }
}
