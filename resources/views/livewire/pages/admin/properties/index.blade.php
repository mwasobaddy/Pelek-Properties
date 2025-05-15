@php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Property;
use function Livewire\Volt\{state, computed};

new #[Layout('components.layouts.app')] class extends Component {
    public function mount()
    {
        $this->authorize('manage-properties');
    }

    #[computed]
    public function properties()
    {
        return Property::with(['propertyType', 'images'])
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
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold">Properties</h2>
                        <a 
                            href="{{ route('admin.properties.manage') }}" 
                            class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors"
                        >
                            Manage Properties
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($this->properties as $property)
                            <div class="bg-white dark:bg-gray-700 rounded-lg shadow overflow-hidden">
                                @if($property->images->isNotEmpty())
                                    <img 
                                        src="{{ $property->images->first()->image_path }}" 
                                        alt="{{ $property->title }}"
                                        class="w-full h-48 object-cover"
                                    >
                                @endif
                                <div class="p-4">
                                    <h3 class="text-lg font-medium">{{ $property->title }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $property->propertyType->name }}</p>
                                    <div class="mt-4 flex justify-between items-center">
                                        <span class="text-primary-600 font-medium">
                                            KES {{ number_format($property->price) }}
                                        </span>
                                        <a 
                                            href="{{ route('admin.properties.photos', $property) }}"
                                            class="text-sm text-primary-600 hover:text-primary-700"
                                        >
                                            Manage Photos
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
