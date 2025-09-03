<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertySocialLink extends Model
{
    protected $fillable = [
        'property_id',
        'platform',
        'url',
        'title'
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
