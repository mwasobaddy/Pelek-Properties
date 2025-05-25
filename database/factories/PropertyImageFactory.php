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
        $basePath = 'images/property_images/placeholders';
        
        return [
            'property_id' => Property::factory(),
            'image_path' => "{$basePath}/property-{$imageNumber}.jpg",
            'thumbnail_path' => "{$basePath}/property-{$imageNumber}-thumb.jpg",
            'is_featured' => $this->faker->boolean(20),
            'display_order' => $this->faker->numberBetween(1, 10),
            'alt_text' => $this->faker->sentence(),
            'metadata' => [
                'optimized' => true,
                'dimensions' => [800, 600],
                'responsive_paths' => [
                    'xs' => [
                        'original' => "{$basePath}/xs_property-{$imageNumber}.jpg",
                        'webp' => "{$basePath}/xs_property-{$imageNumber}.webp"
                    ],
                    'sm' => [
                        'original' => "{$basePath}/sm_property-{$imageNumber}.jpg",
                        'webp' => "{$basePath}/sm_property-{$imageNumber}.webp"
                    ],
                    'md' => [
                        'original' => "{$basePath}/md_property-{$imageNumber}.jpg",
                        'webp' => "{$basePath}/md_property-{$imageNumber}.webp"
                    ],
                    'lg' => [
                        'original' => "{$basePath}/lg_property-{$imageNumber}.jpg",
                        'webp' => "{$basePath}/lg_property-{$imageNumber}.webp"
                    ],
                ]
            ]
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
