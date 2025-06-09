<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PropertyType>
 */
class PropertyTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $propertyTypes = [
            'Apartment' => 'Modern living spaces in multi-unit buildings, perfect for urban dwellers.',
            'House' => 'Traditional single-family homes with private space and gardens.',
            'Villa' => 'Luxury standalone properties with premium amenities and space.',
            'Townhouse' => 'Multi-level homes sharing walls with adjacent properties.',
            'Studio' => 'Compact, open-plan living spaces ideal for singles or couples.',
            'Penthouse' => 'Premium top-floor apartments with exclusive features.',
            'Cottage' => 'Charming small houses, often in rural or scenic locations.',
            'Mansion' => 'Large, luxurious properties with extensive grounds.',
            'Bungalow' => 'Single-story homes perfect for easy living.',
            'Duplex' => 'Two-story apartments with separate entrances.',

            // 'Commercial' => 'Properties designed for business use, including offices, retail spaces, and warehouses.',
            'Office' => 'Professional spaces for businesses, including coworking and private offices.',
            'Retail' => 'Commercial spaces for shops, boutiques, and service providers.',
            'Industrial' => 'Properties for manufacturing, storage, and distribution.',
            'Warehouse' => 'Large storage spaces for goods and inventory.',
            'Mixed Use' => 'Properties that combine residential, commercial, and/or industrial spaces.',
            'Land' => 'Vacant plots for development, agriculture, or investment purposes.',
            'Plot' => 'Vacant land available for construction or investment.',

        ];

        $name = $this->faker->unique()->randomElement(array_keys($propertyTypes));
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $propertyTypes[$name],
        ];
    }
}
