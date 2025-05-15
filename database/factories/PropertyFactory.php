<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->words(4, true);
        $listingType = $this->faker->randomElement(['sale', 'rent', 'airbnb', 'commercial']);
        $basePrice = $this->faker->numberBetween(50000, 1000000);
        $type = match ($listingType) {
            'sale' => 'residential',
            'rent' => 'residential',
            'airbnb' => 'vacation',
            'commercial' => $this->faker->randomElement(['office', 'retail', 'industrial', 'warehouse', 'mixed_use']),
        };

        return [
            'user_id' => User::factory(),
            'property_type_id' => PropertyType::factory(),
            'title' => ucwords($title),
            'slug' => Str::slug($title),
            'description' => $this->faker->paragraphs(3, true),
            'type' => $type,
            'price' => $basePrice,
            'location' => $this->faker->address(),
            'neighborhood' => $this->faker->city(),
            'city' => 'Nairobi',
            'bedrooms' => $this->faker->numberBetween(1, 6),
            'bathrooms' => $this->faker->numberBetween(1, 4),
            'size' => $size = $this->faker->numberBetween(30, 1000),
            'square_range' => match(true) {
                $size <= 50 => '0-50',
                $size <= 100 => '50-100',
                $size <= 200 => '100-200',
                $size <= 300 => '200-300',
                $size <= 500 => '300-500',
                default => '500+'
            },
            'floors' => $this->faker->numberBetween(1, 20),
            'listing_type' => $listingType,
            'status' => $this->faker->randomElement(['available', 'under_contract', 'sold', 'rented']),
            'is_featured' => $this->faker->boolean(20),
            'additional_features' => $this->generateAdditionalFeatures(),
            'rental_price_daily' => $listingType === 'rent' ? $basePrice * 0.0005 : null,
            'rental_price_monthly' => $listingType === 'rent' ? $basePrice * 0.01 : null,
            'airbnb_price_nightly' => $listingType === 'airbnb' ? $basePrice * 0.0007 : null,
            'airbnb_price_weekly' => $listingType === 'airbnb' ? $basePrice * 0.004 : null,
            'airbnb_price_monthly' => $listingType === 'airbnb' ? $basePrice * 0.015 : null,
            'availability_calendar' => $listingType === 'airbnb' ? $this->generateAvailabilityCalendar() : null,
            'whatsapp_number' => $this->faker->phoneNumber(),
        ];
    }

    /**
     * Generate additional features for the property.
     */
    private function generateAdditionalFeatures(): array
    {
        $features = [
            'Parking' => $this->faker->boolean(80),
            'Security' => $this->faker->boolean(90),
            'Internet' => $this->faker->boolean(85),
            'Furnished' => $this->faker->boolean(60),
            'Pool' => $this->faker->boolean(30),
            'Garden' => $this->faker->boolean(40),
            'Gym' => $this->faker->boolean(25),
            'Solar Water Heating' => $this->faker->boolean(70),
        ];

        return array_filter($features, fn($value) => $value === true);
    }

    /**
     * Generate a sample availability calendar.
     */
    private function generateAvailabilityCalendar(): array
    {
        $calendar = [];
        $startDate = now();
        
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $calendar[$date] = $this->faker->boolean(80) ? 'available' : 'booked';
        }

        return $calendar;
    }

    /**
     * Indicate that the property is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the property is for sale.
     */
    public function forSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_type' => 'sale',
            'type' => 'residential',
            'rental_price_daily' => null,
            'rental_price_monthly' => null,
            'airbnb_price_nightly' => null,
            'airbnb_price_weekly' => null,
            'airbnb_price_monthly' => null,
            'availability_calendar' => null,
        ]);
    }

    /**
     * Indicate that the property is for rent.
     */
    public function forRent(): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_type' => 'rent',
            'type' => 'residential',
            'airbnb_price_nightly' => null,
            'airbnb_price_weekly' => null,
            'airbnb_price_monthly' => null,
            'availability_calendar' => null,
        ]);
    }

    /**
     * Indicate that the property is for Airbnb.
     */
    public function forAirbnb(): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_type' => 'airbnb',
            'type' => 'vacation',
        ]);
    }
    /**
     * Indicate that the property is for commercial use.
     */
    public function forCommercial(): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_type' => 'commercial',
            'type' => $this->faker->randomElement(['office', 'retail', 'industrial', 'warehouse', 'mixed_use']),
            'rental_price_daily' => null,
            'rental_price_monthly' => null,
            'airbnb_price_nightly' => null,
            'airbnb_price_weekly' => null,
            'airbnb_price_monthly' => null,
            'availability_calendar' => null,
        ]);
    }

    /**
     * Indicate that the property is sold.
     */
    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sold',
        ]);
    }
}
