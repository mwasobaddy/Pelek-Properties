<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PropertyImage>
 */
class PropertyImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a random number for the placeholder image
        $imageNumber = $this->faker->numberBetween(1, 5);
        $basePath = 'properties/placeholders';
        
        return [
            'property_id' => Property::factory(),
            'image_path' => "{$basePath}/property-{$imageNumber}.jpg",
            'thumbnail_path' => "{$basePath}/property-{$imageNumber}-thumb.jpg",
            'is_featured' => $this->faker->boolean(20),
            'display_order' => $this->faker->numberBetween(1, 10),
            'alt_text' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that the image is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'display_order' => 1,
        ]);
    }
}
