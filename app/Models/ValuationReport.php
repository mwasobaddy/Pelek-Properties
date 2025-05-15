<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValuationReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'valuation_request_id',
        'market_analysis_id',
        'estimated_value',
        'justification',
        'comparable_properties',
        'valuation_factors',
        'confidence_level',
        'valid_until',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'comparable_properties' => 'array',
        'valuation_factors' => 'array',
        'valid_until' => 'date',
    ];

    public function valuationRequest(): BelongsTo
    {
        return $this->belongsTo(ValuationRequest::class);
    }

    public function marketAnalysis(): BelongsTo
    {
        return $this->belongsTo(MarketAnalysis::class);
    }

    public function isValid(): bool
    {
        return now()->lte($this->valid_until);
    }

    public function getDaysUntilExpiry(): int
    {
        return now()->diffInDays($this->valid_until, false);
    }
}
