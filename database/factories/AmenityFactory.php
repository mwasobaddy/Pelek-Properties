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
                'Parking' => 'clipboard-document-list',
                'Air Conditioning' => 'clipboard-document-list',
                'Heating' => 'clipboard-document-list',
                'Internet' => 'clipboard-document-list',
                'Cable TV' => 'clipboard-document-list',
            ],
            'luxury' => [
                'Swimming Pool' => 'clipboard-document-list',
                'Gym' => 'clipboard-document-list',
                'Sauna' => 'clipboard-document-list',
                'Tennis Court' => 'clipboard-document-list',
                'Cinema Room' => 'clipboard-document-list',
            ],
            'outdoor' => [
                'Garden' => 'clipboard-document-list',
                'Balcony' => 'clipboard-document-list',
                'BBQ' => 'clipboard-document-list',
                'Terrace' => 'clipboard-document-list',
                'Private Beach' => 'clipboard-document-list',
            ],
            'security' => [
                '24/7 Security' => 'clipboard-document-list',
                'CCTV' => 'clipboard-document-list',
                'Gated Community' => 'clipboard-document-list',
                'Intercom' => 'clipboard-document-list',
                'Security Alarm' => 'clipboard-document-list',
            ],
            'utility' => [
                'Laundry' => 'clipboard-document-list',
                'Storage' => 'clipboard-document-list',
                'Elevator' => 'clipboard-document-list',
                'Generator' => 'clipboard-document-list',
                'Water Tank' => 'clipboard-document-list',
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
