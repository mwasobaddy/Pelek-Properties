<?php

namespace App\Console\Commands;

use App\Models\Property;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Support\Facades\Storage;

class GenerateImageSitemap extends Command
{
    protected $signature = 'sitemap:generate-images';
    protected $description = 'Generate a sitemap specifically for property images';

    public function handle()
    {
        $this->info('Generating image sitemap...');

        $sitemap = Sitemap::create();

        Property::with(['images' => function ($query) {
            // Only get featured images for properties
            $query->where('is_featured', true);
        }])->chunk(100, function ($properties) use ($sitemap) {
            foreach ($properties as $property) {
                if ($property->featuredImage) {
                    $imageUrl = url(Storage::disk('public')->url($property->featuredImage->image_path));
                    
                    $sitemap->add(
                        Url::create(route('properties.show', $property))
                            ->addImage($imageUrl, [
                                'title' => $property->title,
                                'caption' => $property->description,
                                'geo_location' => $property->location,
                            ])
                    );
                }
            }
        });

        $sitemap->writeToFile(public_path('image-sitemap.xml'));
        $this->info('Image sitemap generated successfully!');
    }
}
