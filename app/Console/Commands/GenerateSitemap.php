<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Location;
use App\Models\Property;
use Illuminate\Console\Command;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sitemap\Sitemap;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap with images and location support';

    private function pingSearchEngines(): void
    {
        $sitemapUrl = config('app.url') . '/sitemap.xml';
        
        // Google
        $googlePing = 'https://www.google.com/ping?sitemap=' . urlencode($sitemapUrl);
        
        // Bing
        $bingPing = 'https://www.bing.com/ping?sitemap=' . urlencode($sitemapUrl);
        
        try {
            $client = new \GuzzleHttp\Client();
            
            // Ping Google
            $response = $client->get($googlePing);
            if ($response->getStatusCode() === 200) {
                $this->info('Successfully pinged Google');
            }
            
            // Ping Bing
            $response = $client->get($bingPing);
            if ($response->getStatusCode() === 200) {
                $this->info('Successfully pinged Bing');
            }
        } catch (\Exception $e) {
            $this->error('Failed to ping search engines: ' . $e->getMessage());
        }
    }

    public function handle(): void
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create()
            // Static Pages
            ->add(Url::create('/')
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/about')
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY))
            ->add(Url::create('/contact')
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY))
            ->add(Url::create('/services/valuation')
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY))
            ->add(Url::create('/services/property-management')
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY))
            // Legal Pages
            ->add(Url::create('/privacy-policy')
                ->setPriority(0.3)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY))
            ->add(Url::create('/terms-of-service')
                ->setPriority(0.3)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY))
            ->add(Url::create('/cookie-policy')
                ->setPriority(0.3)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY))
            // Property Type Pages
            ->add(Url::create('/properties')
                ->setPriority(0.9)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/properties?propertyListingType=sale')
                ->setPriority(0.9)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/properties?propertyListingType=rent')
                ->setPriority(0.9)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/properties?propertyListingType=commercial')
                ->setPriority(0.9)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            ->add(Url::create('/properties?propertyListingType=airbnb')
                ->setPriority(0.9)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
            // Blog Pages
            ->add(Url::create('/blog')
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));

        // Add all published blog posts with featured images
        BlogPost::published()->get()->each(function (BlogPost $post) use ($sitemap) {
            $url = Url::create("/blog/{$post->slug}")
                ->setPriority(0.7)
                ->setLastModificationDate($post->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);

            if ($post->featured_image) {
                $url->addImage(
                    asset('blog_images/' . ltrim($post->featured_image, '/')),
                    $post->title,
                    '',
                    (string) $post->excerpt
                );
            }

            $sitemap->add($url);
        });

        // Add all active property listings with images
        Property::where('status', 'available')->get()->each(function (Property $property) use ($sitemap) {
            $url = Url::create("/properties/{$property->id}")
                ->setPriority(0.9)
                ->setLastModificationDate($property->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY);

            // Add featured image
            if ($property->featured_image) {
                $url->addImage(
                    asset('property_images/' . ltrim($property->featured_image, '/')),
                    $property->title,
                    '',
                    (string) $property->description
                );
            }

            // Add gallery images
            $property->images->each(function ($image) use ($url) {
                $url->addImage(
                    asset('property_images/' . ltrim($image->path, '/')),
                    $image->title ?? '',
                    '',
                    $image->caption ?? ''
                );
            });

            $sitemap->add($url);
        });

        // Add location pages
        if (class_exists(Location::class)) {
            Location::where('status', 'active')->get()->each(function (Location $location) use ($sitemap) {
                $sitemap->add(
                    Url::create("/properties/location/{$location->slug}")
                        ->setPriority(0.8)
                        ->setLastModificationDate($location->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                );
            });
        }

        // Generate and save the sitemap
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully!');
        
        // Ping search engines
        $this->pingSearchEngines();

        $this->pingSearchEngines();
    }
}
