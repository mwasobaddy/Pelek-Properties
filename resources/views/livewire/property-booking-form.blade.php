<x-guest-layout>
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
            <div class="px-4 py-5 sm:p-6 space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Book {{ $property->title }}
                </h3>

                <!-- Calendar View -->
                <livewire:admin.property-availability-calendar 
                    :property="$property" 
                    :readonly="true" 
                />

                <!-- Booking Form -->
                <form wire:submit.prevent="createBooking" class="space-y-6">
                    <!-- Dates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label for="checkIn" value="Check In" />
                            <x-input 
                                type="date" 
                                wire:model.live="checkIn" 
                                id="checkIn"
                                class="mt-1 block w-full"
                                min="{{ now()->format('Y-m-d') }}"
                            />
                            <x-input-error for="checkIn" class="mt-2" />
                        </div>

                        <div>
                            <x-label for="checkOut" value="Check Out" />
                            <x-input 
                                type="date" 
                                wire:model.live="checkOut" 
                                id="checkOut"
                                class="mt-1 block w-full"
                                min="{{ now()->addDay()->format('Y-m-d') }}"
                            />
                            <x-input-error for="checkOut" class="mt-2" />
                        </div>
                    </div>

                    <!-- Guest Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label for="guestName" value="Full Name" />
                            <x-input 
                                type="text" 
                                wire:model="guestName" 
                                id="guestName"
                                class="mt-1 block w-full"
                                placeholder="Enter your full name"
                            />
                            <x-input-error for="guestName" class="mt-2" />
                        </div>

                        <div>
                            <x-label for="guestPhone" value="Phone Number" />
                            <x-input 
                                type="tel" 
                                wire:model="guestPhone" 
                                id="guestPhone"
                                class="mt-1 block w-full"
                                placeholder="+254 XXX XXX XXX"
                            />
                            <x-input-error for="guestPhone" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-label for="guestEmail" value="Email Address" />
                        <x-input 
                            type="email" 
                            wire:model="guestEmail" 
                            id="guestEmail"
                            class="mt-1 block w-full"
                            placeholder="your.email@example.com"
                        />
                        <x-input-error for="guestEmail" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="notes" value="Special Requests (Optional)" />
                        <x-textarea
                            wire:model="notes"
                            id="notes"
                            rows="3"
                            class="mt-1 block w-full"
                            placeholder="Any special requirements or requests..."
                        ></x-textarea>
                        <x-input-error for="notes" class="mt-2" />
                    </div>

                    <!-- Price Summary -->
                    @if($isDateRangeValid && $totalAmount > 0)
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Price Summary</h4>
                            <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                <div class="flex justify-between">
                                    <span>Total Amount:</span>
                                    <span class="font-medium">KES {{ number_format($totalAmount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-between items-center">
                        <button
                            type="button"
                            wire:click="whatsAppContact"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                            Chat on WhatsApp
                        </button>
                        <x-button>
                            Confirm Booking
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div
        x-data="{ show: false, bookingId: null }"
        x-show="show"
        x-on:booking-created.window="show = true; bookingId = $event.detail.bookingId"
        class="fixed inset-0 z-50"
        style="display: none;"
    >
        <div class="absolute inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div>
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                Booking Confirmed!
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Your booking has been confirmed. You will receive a confirmation email shortly with all the details.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6">
                        <x-button class="w-full justify-center" @click="show = false">
                            Done
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
