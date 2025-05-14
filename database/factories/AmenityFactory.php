<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Amenity>
 */
class AmenityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amenities = [
            'basic' => [
                'Parking' => 'car',
                'Air Conditioning' => 'snowflake',
                'Heating' => 'fire',
                'Internet' => 'wifi',
                'Cable TV' => 'tv',
            ],
            'luxury' => [
                'Swimming Pool' => 'water',
                'Gym' => 'dumbbell',
                'Sauna' => 'hot-tub',
                'Tennis Court' => 'tennis',
                'Cinema Room' => 'film',
            ],
            'outdoor' => [
                'Garden' => 'tree',
                'Balcony' => 'door-open',
                'BBQ' => 'fire',
                'Terrace' => 'sun',
                'Private Beach' => 'umbrella-beach',
            ],
            'security' => [
                '24/7 Security' => 'shield',
                'CCTV' => 'camera',
                'Gated Community' => 'lock',
                'Intercom' => 'phone',
                'Security Alarm' => 'bell',
            ],
            'utility' => [
                'Laundry' => 'washing-machine',
                'Storage' => 'box',
                'Elevator' => 'arrow-up',
                'Generator' => 'bolt',
                'Water Tank' => 'water',
            ],
        ];

        $category = $this->faker->randomElement(array_keys($amenities));
        $name = $this->faker->unique()->randomElement(array_keys($amenities[$category]));
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'icon' => $amenities[$category][$name],
            'category' => $category,
        ];
    }

    /**
     * Indicate that the amenity is of a specific category.
     */
    public function ofCategory(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }
}
