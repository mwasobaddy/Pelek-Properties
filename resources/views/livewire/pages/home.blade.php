<?php

use Livewire\Volt\Component;

new #[Layout('components.layouts.guest')] class extends Component {
    public function with(): array
    {
        return [];
    }
} ?>

<div>
    {{-- Hero Section with Search --}}
    <livewire:components.search-hero />

    {{-- Featured Properties Section --}}
    <livewire:components.featured-properties />

    {{-- Property Categories Section --}}
    <livewire:components.property-categories />

    {{-- Call to Action Section --}}
    <livewire:components.call-to-action />
</div>
