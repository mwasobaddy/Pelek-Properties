<?php

use App\Services\SEOService;
use Livewire\Attributes\Layout;
use function Livewire\Volt\{state};

new #[Layout('components.layouts.guest')] class extends Component {
    public function mount(SEOService $seoService)
    {
        $seoService->setServiceMeta('valuation');
    }
}
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-3xl mx-auto text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Property Valuation Services</h1>
        <p class="text-lg text-gray-600 dark:text-gray-300">Get accurate, market-driven valuations for your property</p>
    </div>

    <div class="grid md:grid-cols-2 gap-8 mb-12">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Instant Valuation</h2>
            <p class="text-gray-600 dark:text-gray-300 mb-6">Get a quick estimate of your property's value based on our advanced algorithm and market data.</p>
            <livewire:components.forms.valuation-calculator />
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Professional Valuation</h2>
            <p class="text-gray-600 dark:text-gray-300 mb-6">Request a detailed valuation report from our property experts.</p>
            <livewire:components.forms.valuation-request-form />
        </div>
    </div>

    <!-- Features -->
    <div class="grid md:grid-cols-3 gap-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
            <div class="w-12 h-12 bg-[#02c9c2] rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Market Analysis</h3>
            <p class="text-gray-600 dark:text-gray-300">Comprehensive analysis of local market trends and comparable properties</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
            <div class="w-12 h-12 bg-[#02c9c2] rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Price Trends</h3>
            <p class="text-gray-600 dark:text-gray-300">Historical price data and future value projections</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
            <div class="w-12 h-12 bg-[#02c9c2] rounded-lg flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Investment Insights</h3>
            <p class="text-gray-600 dark:text-gray-300">Expert recommendations for maximizing property value</p>
        </div>
    </div>
</div>
