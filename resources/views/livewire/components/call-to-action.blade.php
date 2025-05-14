<?php

use Livewire\Volt\Component;

new class extends Component {
    public string $listingType = '';
    public string $phoneNumber = '+254712345678'; // Replace with actual business phone number

    public function showWhatsAppModal($type) {
        $this->listingType = $type;
        $this->dispatch('modal-show', [
            'name' => 'whatsapp-modal',
            'scope' => $this->getId()
        ]);
    }

    public function redirectToWhatsApp() {
        $messageTemplate = match($this->listingType) {
            'sale' => "Hi, I would like to list my property for sale on Pelek Properties.",
            'rent' => "Hi, I would like to list my property for rent on Pelek Properties.",
            'airbnb' => "Hi, I would like to list my property on Airbnb through Pelek Properties.",
            'airbnb-info' => "Hi, I would like to learn more about your Airbnb management services.",
            'management' => "Hi, I would like to learn more about your property management services.",
            'inquiry' => "Hi, I would like to inquire about your services at Pelek Properties.",
            default => "Hi, I would like to learn more about your services at Pelek Properties."
        };
        
        $message = urlencode($messageTemplate);
        return redirect()->away("https://wa.me/{$this->phoneNumber}?text={$message}");
    }
    
    public array $actions = [
        [
            'title' => 'List Your Property',
            'description' => 'Whether you\'re selling or renting out your property, let us help you reach potential clients.',
            'icon' => 'squares-plus',
            'color' => 'emerald',
            'buttons' => [
                [
                    'text' => 'List for Sale',
                    'action' => 'showWhatsAppModal',
                    'params' => 'sale',
                    'style' => 'primary'
                ],
                [
                    'text' => 'List for Rent',
                    'action' => 'showWhatsAppModal',
                    'params' => 'rent',
                    'style' => 'secondary'
                ]
            ]
        ],
        [
            'title' => 'Property Management',
            'description' => 'Let us handle your property with our comprehensive management services.',
            'icon' => 'building-office',
            'color' => 'blue',
            'buttons' => [
                [
                    'text' => 'Get Management Services',
                    'action' => 'showWhatsAppModal',
                    'params' => 'management',
                    'style' => 'primary'
                ],
                [
                    'text' => 'Contact Us',
                    'action' => 'showWhatsAppModal',
                    'params' => 'inquiry',
                    'style' => 'secondary'
                ]
            ]
        ],
        [
            'title' => 'List on Airbnb',
            'description' => 'Want to list your Airbnb property? Contact us for professional management and listing services.',
            'icon' => 'calendar-days',
            'color' => 'purple',
            'buttons' => [
                [
                    'text' => 'List Your Airbnb',
                    'action' => 'showWhatsAppModal',
                    'params' => 'airbnb',
                    'style' => 'primary'
                ],
                [
                    'text' => 'Learn About Airbnb Services',
                    'action' => 'showWhatsAppModal',
                    'params' => 'airbnb-info',
                    'style' => 'secondary'
                ]
            ]
        ]
    ];
} ?>

<div>
    <!-- Add your property Hero Section -->
    <div class="bg-center bg-no-repeat bg-cover py-16 lg:py-24" style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('storage/hero-cover.jpg');">
        <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
            <div class="w-full text-center">
                <h1 class="mb-4 text-4xl font-bold tracking-tight leading-none text-white md:text-5xl lg:text-6xl">
                    Are you looking to list your property?</h1>
                <p class="mb-8 text-lg font-normal text-gray-300 lg:text-xl sm:px-16 lg:px-48">
                    We offer comprehensive property management solutions to help you get the most out of your investment.</p>
                <div class="flex flex-col space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4 justify-center">
                    <flux:button wire:click="showWhatsAppModal('sale')"
                        class="inline-flex justify-center items-center py-3 px-5 text-base font-medium text-center text-white rounded-lg bg-emerald-700 hover:bg-emerald-800 focus:ring-4 focus:ring-emerald-900">
                        List Property For Sale
                        <svg class="w-3.5 h-3.5 ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M1 5h12m0 0L9 1m4 4L9 9" />
                        </svg>
                    </flux:button>
                    <flux:button wire:click="showWhatsAppModal('rent')"
                        class="inline-flex justify-center items-center py-3 px-5 text-base font-medium text-center text-white rounded-lg border border-white hover:text-gray-900 hover:bg-gray-100 focus:ring-4 focus:ring-gray-400">
                        List Property For Rent
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    <div class="py-16 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    Ready to Get Started?
                </h2>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                    Choose how you'd like to work with Pelek Properties
                </p>
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                @foreach($actions as $action)
                    <div class="relative group">
                        <div class="h-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm transition duration-300 hover:shadow-lg">
                            {{-- Icon --}}
                            <div class="mb-6">
                                <div class="inline-flex items-center justify-center p-3 rounded-xl
                                    {{ $action['color'] === 'emerald' ? 'bg-emerald-100 dark:bg-emerald-800/30' : '' }}
                                    {{ $action['color'] === 'blue' ? 'bg-blue-100 dark:bg-blue-800/30' : '' }}
                                    {{ $action['color'] === 'purple' ? 'bg-purple-100 dark:bg-purple-800/30' : '' }}"
                                >
                                    <flux:icon 
                                        :name="$action['icon']" 
                                        class="h-8 w-8
                                            {{ $action['color'] === 'emerald' ? 'text-emerald-600 dark:text-emerald-400' : '' }}
                                            {{ $action['color'] === 'blue' ? 'text-blue-600 dark:text-blue-400' : '' }}
                                            {{ $action['color'] === 'purple' ? 'text-purple-600 dark:text-purple-400' : '' }}"
                                    />
                                </div>
                            </div>

                            {{-- Content --}}
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                                {{ $action['title'] }}
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-8">
                                {{ $action['description'] }}
                            </p>

                            {{-- Buttons --}}
                            <div class="space-y-3 sm:space-y-0 sm:space-x-3 sm:flex mt-auto">
                                @foreach($action['buttons'] as $button)
                                    @if(isset($button['action']))
                                        <flux:button 
                                            wire:click="{{ $button['action'] }}('{{ is_string($button['params']) ? $button['params'] : json_encode($button['params']) }}')"
                                            @class([
                                                'flex-1 inline-flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900',
                                                'text-white shadow-sm' => $button['style'] === 'primary',
                                                'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500' => $button['style'] === 'primary' && $action['color'] === 'emerald',
                                                'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' => $button['style'] === 'primary' && $action['color'] === 'blue',
                                                'bg-purple-600 hover:bg-purple-700 focus:ring-purple-500' => $button['style'] === 'primary' && $action['color'] === 'purple',
                                                'text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600' => $button['style'] === 'secondary',
                                            ])
                                        >
                                            {{ $button['text'] }}
                                        </flux:button>
                                    @else
                                        <a 
                                            href="{{ isset($button['route']) ? route($button['route'], $button['params'] ?? []) : '#' }}"
                                            @class([
                                                'flex-1 inline-flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900',
                                                'text-white shadow-sm' => $button['style'] === 'primary',
                                                'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500' => $button['style'] === 'primary' && $action['color'] === 'emerald',
                                                'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' => $button['style'] === 'primary' && $action['color'] === 'blue',
                                                'bg-purple-600 hover:bg-purple-700 focus:ring-purple-500' => $button['style'] === 'primary' && $action['color'] === 'purple',
                                                'text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600' => $button['style'] === 'secondary',
                                            ])
                                        >
                                            {{ $button['text'] }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Popular Property Services -->
    <div class="py-8 px-4 mx-auto max-w-screen-xl sm:py-16 lg:px-6">
        <div class="max-w-screen-md mb-8 lg:mb-16">
            <h2 class="mb-4 text-4xl tracking-tight font-extrabold text-gray-900">Let us help you with your property</h2>
            <p class="text-gray-500 sm:text-xl">We offer a wide range of property management services to help you get the most
                out of your investment.</p>
        </div>
        <div class="space-y-8 md:grid md:grid-cols-2 lg:grid-cols-3 md:gap-12 md:space-y-0">
            <div>
                <div
                    class="flex justify-center items-center mb-4 w-10 h-10 rounded-full bg-emerald-100 lg:h-12 lg:w-12">
                    <svg class="w-5 h-5 text-emerald-600 lg:w-6 lg:h-6" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h3 class="mb-2 text-xl font-bold text-gray-900">Property Management</h3>
                <p class="text-gray-500">Comprehensive property management services for both residential and commercial
                    properties.</p>
                <flux:button wire:click="showWhatsAppModal('management')"
                    class="mt-4 text-emerald-600 hover:text-emerald-800 font-medium text-sm inline-flex items-center">
                    Learn more about property management
                    <svg class="w-3.5 h-3.5 ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M1 5h12m0 0L9 1m4 4L9 9" />
                    </svg>
                </flux:button>
            </div>
            <div>
                <div
                    class="flex justify-center items-center mb-4 w-10 h-10 rounded-full bg-emerald-100 lg:h-12 lg:w-12">
                    <svg class="w-5 h-5 text-emerald-600 lg:w-6 lg:h-6" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z">
                        </path>
                    </svg>
                </div>
                <h3 class="mb-2 text-xl font-bold text-gray-900">Airbnb Management</h3>
                <p class="text-gray-500">End-to-end Airbnb property management services for hassle-free hosting.</p>
                <flux:button wire:click="showWhatsAppModal('airbnb-info')"
                    class="mt-4 text-emerald-600 hover:text-emerald-800 font-medium text-sm inline-flex items-center">
                    Learn more about Airbnb management
                    <svg class="w-3.5 h-3.5 ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M1 5h12m0 0L9 1m4 4L9 9" />
                    </svg>
                </flux:button>
            </div>
            <div>
                <div
                    class="flex justify-center items-center mb-4 w-10 h-10 rounded-full bg-emerald-100 lg:h-12 lg:w-12">
                    <svg class="w-5 h-5 text-emerald-600 lg:w-6 lg:h-6" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z"
                            clip-rule="evenodd"></path>
                        <path
                            d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z">
                        </path>
                    </svg>
                </div>
                <h3 class="mb-2 text-xl font-bold text-gray-900">List Property for Airbnb</h3>
                <p class="text-gray-500">Convert your property into a profitable Airbnb listing with our expert services.</p>
                <flux:button wire:click="showWhatsAppModal('airbnb')"
                    class="mt-4 text-emerald-600 hover:text-emerald-800 font-medium text-sm inline-flex items-center">
                    List your property for Airbnb
                    <svg class="w-3.5 h-3.5 ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M1 5h12m0 0L9 1m4 4L9 9" />
                    </svg>
                </flux:button>
            </div>
        </div>
    </div>

    <!-- WhatsApp Contact Modal -->
    <flux:modal name="whatsapp-modal" :scope="$this->getId()" dismissible>
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                        <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Continue to WhatsApp</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                You will be redirected to WhatsApp to continue your conversation with our team.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <flux:button wire:click="redirectToWhatsApp" type="button" class="inline-flex w-full justify-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 sm:ml-3 sm:w-auto">
                    Continue
                </flux:button>
                <flux:button type="button" @click="$dispatch('modal-hide', { name: 'whatsapp-modal', scope: '{{ $this->getId() }}' })" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                    Cancel
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>