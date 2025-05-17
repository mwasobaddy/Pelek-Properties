<?php

use function Livewire\Volt\{state, mount};
use App\Models\ValuationRequest;
use App\Models\ValuationReport;
use App\Services\PropertyService;

state([
    'valuationRequests' => [],
    'valuationReports' => [],
    'filterStatus' => 'all'
]);

mount(function () {
    $this->loadValuations();
});

$loadValuations = function () {
    $this->valuationRequests = ValuationRequest::with('property')
        ->when($this->filterStatus !== 'all', function ($query) {
            $query->where('status', $this->filterStatus);
        })
        ->latest()
        ->get();

    $this->valuationReports = ValuationReport::with(['valuationRequest', 'marketAnalysis'])
        ->latest()
        ->get();
};

?>

<div class="p-6 space-y-6">
    <header class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            {{ __('Property Valuations') }}
        </h1>
        
        <flux:button 
            primary 
            icon="plus"
            :href="route('admin.management.valuations.create')"
            wire:navigate
        >
            {{ __('New Valuation Request') }}
        </flux:button>
    </header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Valuation Requests -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('Valuation Requests') }}
                </h2>
                
                <div class="mt-4 space-y-4">
                    @foreach($this->valuationRequests as $request)
                        <div class="p-4 bg-gray-50 dark:bg-zinc-900 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-gray-900 dark:text-white">
                                        {{ $request->property->title }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('Status') }}: 
                                        <span class="font-medium text-{{ $request->status === 'completed' ? 'green' : 'amber' }}-600">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </p>
                                </div>
                                <flux:button 
                                    secondary 
                                    icon="eye"
                                    :href="route('admin.management.valuations.show', $request)"
                                    wire:navigate
                                >
                                    {{ __('View Details') }}
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Valuation Reports -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('Valuation Reports') }}
                </h2>
                
                <div class="mt-4 space-y-4">
                    @foreach($this->valuationReports as $report)
                        <div class="p-4 bg-gray-50 dark:bg-zinc-900 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-gray-900 dark:text-white">
                                        {{ $report->valuationRequest->property->title }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('Estimated Value') }}: 
                                        <span class="font-medium">
                                            ${{ number_format($report->estimated_value, 2) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2 rtl:space-x-reverse">
                                    <flux:button 
                                        secondary 
                                        icon="arrow-down-tray"
                                        :href="route('admin.management.valuations.report.download', $report)"
                                    >
                                        {{ __('Download') }}
                                    </flux:button>
                                    <flux:button 
                                        secondary 
                                        icon="eye"
                                        :href="route('admin.management.valuations.report.show', $report)"
                                        wire:navigate
                                    >
                                        {{ __('View') }}
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
