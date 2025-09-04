<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;

new #[Layout('components.layouts.guest')] class extends Component {
    public $name = '';
    public $email = '';
    public $phone = '';
    public $subject = '';
    public $message = '';
    public $selectedService = '';

    // Available services
    public array $services = [
        'property-management' => 'Property Management',
        'real-estate-sales' => 'Real Estate Sales',
        'valuation' => 'Property Valuation',
        'general' => 'General Inquiry'
    ];

    public function submitForm()
    {
        $this->validate([
            'name' => 'required|min:2',
            'email' => 'required|email',
            'phone' => 'required',
            'subject' => 'required|min:5',
            'message' => 'required|min:10',
            'selectedService' => 'required'
        ]);

        // Prepare form data for email
        $formData = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'subject' => $this->subject,
            'message' => $this->message,
            'selectedService' => $this->selectedService,
            'services' => $this->services,
            'submitted_at' => now()->format('Y-m-d H:i:s')
        ];

        try {
            // Send email to sales team
            Mail::to('sales@pelekproperties.co.ke')->send(new ContactFormMail($formData));

            // Also send to admin if different from sales
            if (config('mail.from.address') !== 'sales@pelekproperties.co.ke') {
                Mail::to(config('mail.from.address'))->send(new ContactFormMail($formData));
            }

            // Send to personal email if configured
            if (env('ADMIN_EMAIL')) {
                Mail::to(env('ADMIN_EMAIL'))->send(new ContactFormMail($formData));
            }

            session()->flash('success', 'Thank you for your message! We have received your inquiry and will get back to you within 24 hours.');

        } catch (\Exception $e) {
            session()->flash('error', 'Sorry, there was an error sending your message. Please try again or contact us directly.');
        }

        $this->reset(['name', 'email', 'phone', 'subject', 'message', 'selectedService']);
    }
} ?>

<div class="min-h-screen bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
    <!-- Enhanced Hero Section with Parallax Effect -->
    <div class="relative overflow-hidden bg-gradient-to-br from-zinc-900 to-[#012e2b] dark:from-zinc-950 dark:to-[#012e2b]">
        <!-- Background elements with parallax effect -->
        <div class="absolute inset-0" x-data="{}"
            x-on:scroll.window="$el.style.transform = `translateY(${window.scrollY * 0.1}px)`">
            <div class="absolute inset-0 bg-gradient-to-br from-zinc-900/85 via-[#012e2b]/75 to-[#02c9c2]/30 backdrop-blur-sm"></div>
            <img src="{{ asset('images/placeholder.webp') }}" alt="Contact Pelek Properties"
                class="h-full w-full object-cover opacity-40">
        </div>

        <!-- Decorative Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -left-40 -top-40 h-96 w-96 rounded-full bg-[#02c9c2]/20 blur-3xl"></div>
            <div class="absolute right-0 bottom-0 h-80 w-80 rounded-full bg-[#02c9c2]/15 blur-3xl"></div>
        </div>

        <!-- Enhanced Content with Animation -->
        <div class="relative mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:px-8 flex justify-center">
            <div class="mx-auto max-w-2xl lg:mx-0" 
                 x-data="{}" 
                 x-intersect="$el.classList.add('animate-fade-in')">
                <span class="inline-flex items-center rounded-full bg-[#02c9c2]/10 px-3 py-1 text-sm font-medium text-[#02c9c2] ring-1 ring-inset ring-[#02c9c2]/20 mb-4 backdrop-blur-md">
                    Get in Touch
                </span>
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl font-display">
                    Contact Us
                </h1>
                <p class="mt-6 text-lg leading-8 text-zinc-300">
                    Have questions about our properties or services? We're here to help. Reach out to our team and we'll get back to you promptly.
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content Section -->
    <div class="py-24 relative">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <!-- Success Message -->
            @if (session()->has('success'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     class="mb-8 rounded-lg bg-green-50 dark:bg-green-900/50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <flux:icon name="check-circle" class="h-5 w-5 text-green-400" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                {{ session('success') }}
                            </p>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button @click="show = false" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 dark:hover:bg-green-800 transition-colors">
                                    <span class="sr-only">Dismiss</span>
                                    <flux:icon name="x-mark" class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Error Message -->
            @if (session()->has('error'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     class="mb-8 rounded-lg bg-red-50 dark:bg-red-900/50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <flux:icon name="exclamation-circle" class="h-5 w-5 text-red-400" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                {{ session('error') }}
                            </p>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button @click="show = false" class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 dark:hover:bg-red-800 transition-colors">
                                    <span class="sr-only">Dismiss</span>
                                    <flux:icon name="x-mark" class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid md:grid-cols-3 gap-12">
                <!-- Contact Information -->
                <div class="md:col-span-1">
                    <div class="space-y-12">
                        <!-- Location -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Location</h3>
                            <div class="space-y-4 text-gray-600 dark:text-gray-300">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1">
                                        <flux:icon name="map-pin" class="h-6 w-6 text-[#02c9c2]" />
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium">Pelek Properties HQ</p>
                                        <p>Nairobi, Kenya</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Details -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Contact Details</h3>
                            <div class="space-y-4 text-gray-600 dark:text-gray-300">
                                <div class="flex items-center">
                                    <flux:icon name="phone" class="h-6 w-6 text-[#02c9c2]" />
                                    <span class="ml-3">+(254) 711614099</span>
                                </div>
                                <div class="flex items-center">
                                    <flux:icon name="envelope" class="h-6 w-6 text-[#02c9c2]" />
                                    <span class="ml-3">sales@pelekproperties.co.ke</span>
                                </div>
                            </div>
                        </div>

                        <!-- Business Hours -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Business Hours</h3>
                            <div class="space-y-2 text-gray-600 dark:text-gray-300">
                                <div class="flex justify-between">
                                    <span>Monday - Friday:</span>
                                    <span>8:00 AM - 6:00 PM</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Saturday:</span>
                                    <span>9:00 AM - 2:00 PM</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Sunday:</span>
                                    <span>Closed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="md:col-span-2">
                    <div class="rounded-2xl bg-white/80 dark:bg-gray-800/50 backdrop-blur-xl p-8 shadow-xl ring-1 ring-black/5 dark:ring-white/10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Send us a Message</h2>
                        
                        <form wire:submit="submitForm" class="space-y-6">
                            <!-- Name & Email Row -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <flux:input 
                                        label="Name"
                                        wire:model="name"
                                        error="{{ $errors->first('name') }}"
                                    />
                                </div>

                                <div>
                                    <flux:input 
                                        type="email"
                                        label="Email"
                                        wire:model="email"
                                        error="{{ $errors->first('email') }}"
                                    />
                                </div>
                            </div>

                            <!-- Phone & Service Row -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <flux:input 
                                        type="tel"
                                        label="Phone"
                                        wire:model="phone"
                                        error="{{ $errors->first('phone') }}"
                                    />
                                </div>

                                <div>
                                    <flux:select
                                        label="Service"
                                        wire:model="selectedService"
                                        error="{{ $errors->first('selectedService') }}"
                                    >
                                        <option value="">Select a service</option>
                                        @foreach($services as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </flux:select>
                                </div>
                            </div>

                            <!-- Subject -->
                            <div>
                                <flux:input 
                                    label="Subject"
                                    wire:model="subject"
                                    error="{{ $errors->first('subject') }}"
                                />
                            </div>

                            <!-- Message -->
                            <div>
                                <flux:textarea
                                    label="Message" 
                                    wire:model="message"
                                    rows="4"
                                    error="{{ $errors->first('message') }}"
                                />
                            </div>

                            <!-- Submit Button -->
                            <div>
                                <button type="submit"
                                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-[#02c9c2] to-[#012e2b] hover:from-[#012e2b] hover:to-[#02c9c2] transition-all duration-300 shadow-md hover:shadow-lg">
                                    Send Message
                                    <flux:icon name="paper-airplane" class="ml-2 -mr-1 h-5 w-5" />
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Section -->
    <div class="py-12 relative">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="rounded-2xl overflow-hidden shadow-xl">
                <!-- Replace the src with your actual Google Maps embed URL -->
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.8193293037416!2d36.8170146!3d-1.2833296!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMcKwMTYnNTkuOSJTIDM2wrA0OSczNy4zIkU!5e0!3m2!1sen!2ske!4v1620834482548!5m2!1sen!2ske"
                    width="100%"
                    height="450"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </div>
</div>
