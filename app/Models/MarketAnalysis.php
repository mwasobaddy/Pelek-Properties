<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',
        'property_type',
        'average_price',
        'price_per_sqft',
        'total_listings',
        'days_on_market',
        'price_trends',
    ];

    protected $casts = [
        'average_price' => 'decimal:2',
        'price_per_sqft' => 'decimal:2',
        'price_trends' => 'array',
    ];

    public function valuationReports(): HasMany
    {
        return $this->hasMany(ValuationReport::class);
    }

    public function getMarketTrend(): string
    {
        if (empty($this->price_trends)) {
            return 'stable';
        }

        $trends = array_values($this->price_trends);
        $latest = end($trends);
        $previous = prev($trends);

        if ($latest > $previous) {
            return 'increasing';
        } elseif ($latest < $previous) {
            return 'decreasing';
        }

        return 'stable';
    }
}
