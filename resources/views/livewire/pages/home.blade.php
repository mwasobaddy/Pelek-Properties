<?php

use App\Services\PropertySearchService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.guest')] class extends Component {
    use WithPagination;

    public function with(): array
    {
        return [];
    }
} ?>

<div>
    {{-- Hero Section with Search --}}
    <livewire:components.search-hero />

    {{-- Property Categories Section --}}
    <livewire:components.property-categories />

    {{-- Featured Properties Section --}}
    <livewire:components.featured-properties />

    {{-- Property Services Section --}}
    <livewire:components.property-services />

    {{-- Call to Action Section --}}
    <livewire:components.call-to-action />
</div>
