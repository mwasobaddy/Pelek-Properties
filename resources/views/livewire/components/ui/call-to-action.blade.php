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
            'accent' => 'from-emerald-400 to-teal-500',
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
            'accent' => 'from-blue-400 to-cyan-500',
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
            'accent' => 'from-purple-400 to-indigo-500',
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

<div class="relative">
    <!-- Enhanced Hero Section with Gradient Overlay -->
    <div class="relative bg-center bg-no-repeat bg-cover py-24 lg:py-32" style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('storage/properties/placeholders/property-4-thumb.jpg');">
        <!-- Decorative Elements -->
        <div class="absolute top-0 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/20 to-transparent rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-gradient-to-tl from-[#02c9c2]/20 to-transparent rounded-full blur-3xl"></div>
        
        <div class="max-w-5xl mx-auto px-6 lg:px-8 relative z-10">
            <div class="text-center">
                <h1 class="mb-6 text-4xl font-extrabold tracking-tight leading-none text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-300 md:text-5xl lg:text-6xl">
                    Ready to List Your Property?
                </h1>
                <p class="mb-10 text-lg font-normal text-gray-300 lg:text-xl max-w-3xl mx-auto leading-relaxed">
                    We offer comprehensive property management solutions to help you maximize your investment returns through expert handling and strategic marketing.
                </p>
                <div class="flex flex-col space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4 justify-center">
                    <button 
                        wire:click="showWhatsAppModal('sale')"
                        class="group inline-flex justify-center items-center py-3 px-6 text-base font-medium text-center text-white rounded-lg bg-gradient-to-r from-[#02c9c2] to-[#012e2b] hover:from-[#012e2b] hover:to-[#02c9c2] transition-all duration-300 shadow-lg hover:shadow-xl focus:ring-4 focus:ring-[#02c9c2]/50"
                    >
                        List Property For Sale
                        <svg class="w-4 h-4 ml-2 transform transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
                        </svg>
                    </button>
                    <button 
                        wire:click="showWhatsAppModal('rent')"
                        class="group inline-flex justify-center items-center py-3 px-6 text-base font-medium text-center text-white rounded-lg border border-white hover:bg-white/10 transition-all duration-300 shadow-md hover:shadow-lg focus:ring-4 focus:ring-white/30"
                    >
                        List Property For Rent
                        <svg class="w-4 h-4 ml-2 opacity-0 transform transition-transform duration-300 group-hover:opacity-100 group-hover:translate-x-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced WhatsApp Contact Modal -->
    <flux:modal name="whatsapp-modal" :scope="$this->getId()" dismissible>
        <div class="relative transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200 dark:border-gray-700">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#02c9c2] to-[#012e2b]"></div>
            <div class="px-6 py-8">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-r from-[#02c9c2] to-[#012e2b] sm:mx-0 sm:h-12 sm:w-12">
                        <flux:icon name="envelope" class="h-7 w-7 text-white" />
                    </div>
                    <div class="mt-4 sm:ml-6 sm:mt-0">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Continue to WhatsApp</h3>
                        <p class="text-base text-gray-600 dark:text-gray-300">
                            You will be redirected to WhatsApp to continue your conversation with our team about 
                            <span class="font-medium">
                                {{ match($listingType) {
                                    'sale' => 'listing your property for sale',
                                    'rent' => 'listing your property for rent',
                                    'airbnb' => 'listing your property on Airbnb',
                                    'airbnb-info' => 'Airbnb management services',
                                    'management' => 'property management services',
                                    'inquiry' => 'our services',
                                    default => 'our services',
                                } }}.
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/30 px-6 py-4 sm:flex sm:flex-row-reverse gap-3">
                <button 
                    wire:click="redirectToWhatsApp" 
                    class="group w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-[#02c9c2] to-[#012e2b] px-6 py-3 text-base font-medium text-white shadow-sm hover:from-[#012e2b] hover:to-[#02c9c2] focus:outline-none focus:ring-2 focus:ring-[#02c9c2] focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-300"
                >
                    Continue to WhatsApp
                    <svg class="w-5 h-5 ml-2 transform transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
                <button 
                    type="button" 
                    @click="$dispatch('modal-hide', { name: 'whatsapp-modal', scope: '{{ $this->getId() }}' })" 
                    class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-lg bg-white px-6 py-3 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700"
                >
                    Cancel
                </button>
            </div>
        </div>
    </flux:modal>
</div>