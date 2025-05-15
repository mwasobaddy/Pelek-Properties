@php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Services\RentalPropertyService;
use function Livewire\Volt\{state, computed};

new #[Layout('components.layouts.app')] class extends Component {
    public function mount()
    {
        $this->authorize('manage-properties');
    }
}
@endphp

<div>
    <!-- Include the rental properties management component -->
    <livewire:admin.manage-rental-properties />
</div>
