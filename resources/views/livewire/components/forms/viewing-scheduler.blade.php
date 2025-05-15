<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Schedule a Viewing</h3>

    <form wire:submit="scheduleViewing" class="space-y-4">
        <div>
            <label for="client_name" class="block text-sm font-medium text-gray-700">Your Name</label>
            <input type="text" id="client_name" wire:model="appointmentData.client_name" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="John Doe">
            @error('appointmentData.client_name') 
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="client_phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
            <input type="tel" id="client_phone" wire:model="appointmentData.client_phone"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="+254 700 000000">
            @error('appointmentData.client_phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="client_email" class="block text-sm font-medium text-gray-700">Email (Optional)</label>
            <input type="email" id="client_email" wire:model="appointmentData.client_email"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="john@example.com">
            @error('appointmentData.client_email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="appointment_date" class="block text-sm font-medium text-gray-700">Preferred Date & Time</label>
            <input type="datetime-local" id="appointment_date" wire:model="appointmentData.appointment_date"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('appointmentData.appointment_date')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Additional Notes (Optional)</label>
            <textarea id="notes" wire:model="appointmentData.notes" rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="Any specific requirements or questions..."></textarea>
            @error('appointmentData.notes')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6">
            <button type="submit"
                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Schedule Viewing
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('viewing-scheduled', (appointmentId) => {
                // Show success message
                const event = new CustomEvent('notify', {
                    detail: {
                        type: 'success',
                        message: 'Viewing appointment scheduled successfully! Our team will contact you shortly.'
                    }
                });
                window.dispatchEvent(event);
            });
        });
    </script>
</div>
