<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ValuationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_type',
        'location',
        'land_size',
        'bedrooms',
        'bathrooms',
        'description',
        'purpose',
        'status',
    ];

    protected $casts = [
        'land_size' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(ValuationReport::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function markAsInProgress(): void
    {
        $this->update(['status' => 'in_progress']);
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }
}
