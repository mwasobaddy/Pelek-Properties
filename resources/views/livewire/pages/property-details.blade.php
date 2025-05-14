<?puse Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use function Livewire\Volt\{state, computed};

new #[Layout('components.layouts.guest')] class extends Component {se App\Models\Property;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use function Livewire\Volt\{state, computed};

new #[Layout('component.layouts.app')] class extends Component {
    public Property $property;

    public function mount(Property $property)
    {
        $this->property = $property->load(['propertyType', 'amenities', 'images']);
    }

    public function with(): array
    {
        return [
            'similarProperties' => computed(function () {
                return Property::query()
                    ->where('property_type_id', $this->property->property_type_id)
                    ->where('id', '!=', $this->property->id)
                    ->with(['propertyType', 'images'])
                    ->take(4)
                    ->get();
            }),
        ];
    }

    public function contactViaWhatsapp()
    {
        $message = "Hi, I'm interested in the property: {$this->property->title}";
        $phoneNumber = config('app.contact_whatsapp');
        $url = "https://wa.me/{$phoneNumber}?text=" . urlencode($message);
        
        return redirect()->away($url);
    }
} ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Property Header -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="relative h-96">
            @if($property->images->isNotEmpty())
                <img 
                    src="{{ $property->images->first()->url }}" 
                    alt="{{ $property->title }}"
                    class="w-full h-full object-cover"
                >
            @endif
            <div class="absolute top-4 right-4">
                <span class="px-3 py-1 text-sm bg-indigo-500 text-white rounded-full">
                    {{ $property->propertyType->name }}
                </span>
            </div>
        </div>

        <div class="p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ $property->title }}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ $property->location }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                        ${{ number_format($property->rental_price_daily) }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">per day</p>
                </div>
            </div>

            <!-- Property Description -->
            <div class="mt-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Description</h2>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ $property->description }}
                </p>
            </div>

            <!-- Amenities -->
            @if($property->amenities->isNotEmpty())
                <div class="mt-8">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Amenities</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($property->amenities as $amenity)
                            <div class="flex items-center space-x-2">
                                <x-flux-icon name="{{ $amenity->icon ?? 'check-circle' }}" class="w-5 h-5 text-indigo-500"/>
                                <span class="text-gray-600 dark:text-gray-400">{{ $amenity->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Contact Button -->
            <div class="mt-8">
                <button
                    wire:click="contactViaWhatsapp"
                    class="w-full md:w-auto px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg flex items-center justify-center space-x-2 transition"
                >
                    <x-flux-icon name="whatsapp" class="w-5 h-5"/>
                    <span>Contact via WhatsApp</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Similar Properties -->
    @if($this->similarProperties->isNotEmpty())
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Similar Properties</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($this->similarProperties as $similarProperty)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                        <div class="relative">
                            @if($similarProperty->images->isNotEmpty())
                                <img 
                                    src="{{ $similarProperty->images->first()->url }}" 
                                    alt="{{ $similarProperty->title }}"
                                    class="w-full h-48 object-cover"
                                >
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                {{ $similarProperty->title }}
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                                {{ Str::limit($similarProperty->description, 100) }}
                            </p>
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                    ${{ number_format($similarProperty->rental_price_daily) }}
                                </span>
                                <a 
                                    href="{{ route('properties.show', $similarProperty) }}" 
                                    class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition"
                                >
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
