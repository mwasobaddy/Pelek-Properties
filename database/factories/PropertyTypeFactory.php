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
        $name = $this->faker->unique()->randomElement([
            'Apartment',
            'House',
            'Villa',
            'Townhouse',
            'Studio',
            'Penthouse',
            'Cottage',
            'Mansion',
            'Bungalow',
            'Duplex'
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
        ];
    }
}
