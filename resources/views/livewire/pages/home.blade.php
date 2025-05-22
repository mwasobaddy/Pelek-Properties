<?php

use App\Services\PropertySearchService;
use App\Services\SEOService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.guest')] class extends Component {
    use WithPagination;

    public function mount(SEOService $seoService)
    {
        $seoService->setHomeMeta();
    }

    public function with(): array
    {
        return [];
    }
} ?>

<div>
    {{-- Hero Section with Search --}}
    <livewire:components.property.search />

    {{-- Property Categories Section --}}
    <livewire:components.property.categories />

    {{-- Featured Properties Section --}}
    <livewire:components.ui.featured-properties />

    {{-- Property Services Section --}}
    <livewire:components.property.services />

    {{-- Call to Action Section --}}
    <livewire:components.ui.call-to-action />
</div>
