@php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\PropertyBooking;
use function Livewire\Volt\{state, computed};

new #[Layout('components.layouts.app')] class extends Component {
    public function mount()
    {
        $this->authorize('manage-bookings');
    }

    #[computed]
    public function bookings()
    {
        return PropertyBooking::with(['property', 'admin'])
            ->latest()
            ->get();
    }
}
@endphp

<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h2 class="text-2xl font-semibold mb-6">Manage Bookings</h2>

                    <livewire:admin.manage-bookings />
                </div>
            </div>
        </div>
    </div>
</div>
