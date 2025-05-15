<?php

namespace App\Livewire;

use App\Services\SalePropertyService;
use Livewire\Component;
use Livewire\WithPagination;

class SalePropertyList extends Component
{
    use WithPagination;

    public $filters = [
        'price_min' => null,
        'price_max' => null,
        'development_status' => null,
        'ownership_type' => null,
        'mortgage_available' => false,
        'has_title_deed' => false,
    ];

    protected $queryString = [
        'filters' => ['except' => [
            'price_min' => null,
            'price_max' => null,
            'development_status' => null,
            'ownership_type' => null,
            'mortgage_available' => false,
            'has_title_deed' => false,
        ]],
    ];

    public function render()
    {
        $salePropertyService = app(SalePropertyService::class);
        $properties = $salePropertyService->getSaleProperties($this->filters);

        return view('livewire.sale-property-list', [
            'properties' => $properties
        ]);
    }

    public function resetFilters()
    {
        $this->filters = [
            'price_min' => null,
            'price_max' => null,
            'development_status' => null,
            'ownership_type' => null,
            'mortgage_available' => false,
            'has_title_deed' => false,
        ];
    }

    public function contactViaWhatsApp($propertyId)
    {
        $property = \App\Models\Property::find($propertyId);
        if (!$property) return;

        $message = "Hello, I'm interested in the property: {$property->title} (Ref: {$property->reference}) listed at {$property->formatted_sale_price}";
        $phone = config('app.whatsapp_number');
        $url = "https://wa.me/{$phone}?text=" . urlencode($message);

        return redirect()->away($url);
    }
}
