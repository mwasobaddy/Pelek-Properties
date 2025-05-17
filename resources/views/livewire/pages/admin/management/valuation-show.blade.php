<?php

use function Livewire\Volt\{state, mount};
use App\Models\ValuationRequest;

state(['valuationRequest' => null]);

mount(function (ValuationRequest $valuationRequest) {
    $this->valuationRequest = $valuationRequest->load([
        'property', 
        'valuationReport', 
        'valuationReport.marketAnalysis'
    ]);
});

?>

<div class="p-6 space-y-6">
    <header class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ __('Valuation Request Details') }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Request ID') }}: #{{ $this->valuationRequest->id }}
            </p>
        </div>
        
        <div class="flex items-center space-x-2 rtl:space-x-reverse">
            <flux:button 
                secondary
                icon="arrow-left"
                :href="route('admin.management.valuations')"
                wire:navigate
            >
                {{ __('Back to List') }}
            </flux:button>

            @if(!$this->valuationRequest->valuationReport)
                <flux:button 
                    primary
                    icon="document-text"
                    :href="route('admin.management.valuations.report.create', $this->valuationRequest)"
                    wire:navigate
                >
                    {{ __('Create Report') }}
                </flux:button>
            @endif
        </div>
    </header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Request Details -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('Request Information') }}
                </h2>
                
                <dl class="mt-4 space-y-4">
                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</dt>
                        <dd class="col-span-2">
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md text-{{ $this->valuationRequest->status === 'completed' ? 'green' : 'amber' }}-700 bg-{{ $this->valuationRequest->status === 'completed' ? 'green' : 'amber' }}-50 ring-1 ring-inset ring-{{ $this->valuationRequest->status === 'completed' ? 'green' : 'amber' }}-600/20">
                                {{ ucfirst($this->valuationRequest->status) }}
                            </span>
                        </dd>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Property') }}</dt>
                        <dd class="col-span-2 text-sm text-gray-900 dark:text-white">
                            {{ $this->valuationRequest->property->title }}
                        </dd>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Location') }}</dt>
                        <dd class="col-span-2 text-sm text-gray-900 dark:text-white">
                            {{ $this->valuationRequest->property->location }}
                        </dd>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Purpose') }}</dt>
                        <dd class="col-span-2 text-sm text-gray-900 dark:text-white">
                            {{ ucfirst($this->valuationRequest->purpose) }}
                        </dd>
                    </div>

                    @if($this->valuationRequest->description)
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Additional Notes') }}</dt>
                            <dd class="col-span-2 text-sm text-gray-900 dark:text-white">
                                {{ $this->valuationRequest->description }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Valuation Report -->
        @if($this->valuationRequest->valuationReport)
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('Valuation Report') }}
                    </h2>

                    <dl class="mt-4 space-y-4">
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Estimated Value') }}</dt>
                            <dd class="col-span-2 text-sm font-medium text-gray-900 dark:text-white">
                                ${{ number_format($this->valuationRequest->valuationReport->estimated_value, 2) }}
                            </dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Confidence Level') }}</dt>
                            <dd class="col-span-2">
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md text-blue-700 bg-blue-50 ring-1 ring-inset ring-blue-600/20">
                                    {{ ucfirst($this->valuationRequest->valuationReport->confidence_level) }}
                                </span>
                            </dd>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Valid Until') }}</dt>
                            <dd class="col-span-2 text-sm text-gray-900 dark:text-white">
                                {{ $this->valuationRequest->valuationReport->valid_until->format('F j, Y') }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('Actions') }}</dt>
                            <dd class="flex items-center space-x-2 rtl:space-x-reverse">
                                <flux:button 
                                    secondary 
                                    icon="eye"
                                    :href="route('admin.management.valuations.report.show', $this->valuationRequest->valuationReport)"
                                    wire:navigate
                                >
                                    {{ __('View Full Report') }}
                                </flux:button>

                                <flux:button 
                                    secondary 
                                    icon="arrow-down-tray"
                                    :href="route('admin.management.valuations.report.download', $this->valuationRequest->valuationReport)"
                                >
                                    {{ __('Download Report') }}
                                </flux:button>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
