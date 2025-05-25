<?php

namespace Database\Factories;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    public function definition(): array
    {
        $imageNumber = $this->faker->numberBetween(1, 5);
        $basePath = 'images/blog_images/placeholders';

        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'featured_image' => "{$basePath}/blog-{$imageNumber}.jpg",
            'thumbnail_image' => "{$basePath}/blog-{$imageNumber}-thumb.jpg",
            'published_at' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween('-1 year', 'now') : null,
            'is_featured' => $this->faker->boolean(20),
            'author_id' => User::factory(),
            'metadata' => [
                'optimized' => true,
                'dimensions' => [1200, 630],
                'responsive_paths' => [
                    'xs' => [
                        'original' => "{$basePath}/xs_blog-{$imageNumber}.jpg",
                        'webp' => "{$basePath}/xs_blog-{$imageNumber}.webp"
                    ],
                    'sm' => [
                        'original' => "{$basePath}/sm_blog-{$imageNumber}.jpg",
                        'webp' => "{$basePath}/sm_blog-{$imageNumber}.webp"
                    ],
                    'md' => [
                        'original' => "{$basePath}/md_blog-{$imageNumber}.jpg",
                        'webp' => "{$basePath}/md_blog-{$imageNumber}.webp"
                    ],
                    'lg' => [
                        'original' => "{$basePath}/lg_blog-{$imageNumber}.jpg",
                        'webp' => "{$basePath}/lg_blog-{$imageNumber}.webp"
                    ]
                ]
            ]
        ];
    }

    public function published(): self
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function draft(): self
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => null,
        ]);
    }

    public function featured(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
