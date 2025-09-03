<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertySocialLink;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertySocialLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all existing properties
        $properties = Property::all();

        if ($properties->isEmpty()) {
            $this->command->warn('No properties found. Please run PropertySeeder first.');
            return;
        }

        $this->command->info("Creating social media links for {$properties->count()} properties...");

        DB::beginTransaction();
        try {
            $createdLinks = 0;

            foreach ($properties as $property) {
                // Randomly decide how many social links to create for this property (0-3)
                $numLinks = rand(0, 3);

                if ($numLinks > 0) {
                    $platforms = $this->getRandomPlatforms($numLinks);

                    foreach ($platforms as $platform) {
                        PropertySocialLink::create([
                            'property_id' => $property->id,
                            'platform' => $platform,
                            'url' => $this->generateSocialUrl($platform, $property),
                            'title' => $this->generateSocialTitle($platform, $property),
                        ]);
                        $createdLinks++;
                    }
                }
            }

            DB::commit();
            $this->command->info("Successfully created {$createdLinks} social media links for properties.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error creating social media links: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get random social media platforms
     */
    private function getRandomPlatforms(int $count): array
    {
        $platforms = [
            'instagram',
            'tiktok',
            'facebook',
            'twitter',
            'youtube',
            'linkedin',
            'pinterest'
        ];

        // Ensure we don't have duplicates
        shuffle($platforms);
        return array_slice($platforms, 0, $count);
    }

    /**
     * Generate realistic social media URL based on platform and property
     */
    private function generateSocialUrl(string $platform, Property $property): string
    {
        $baseUrls = [
            'instagram' => 'https://instagram.com/p/',
            'tiktok' => 'https://tiktok.com/@pelekproperties/video/',
            'facebook' => 'https://facebook.com/pelekproperties/posts/',
            'twitter' => 'https://twitter.com/pelekproperties/status/',
            'youtube' => 'https://youtube.com/watch?v=',
            'linkedin' => 'https://linkedin.com/posts/pelekproperties/',
            'pinterest' => 'https://pinterest.com/pin/'
        ];

        $propertySlug = str_replace(' ', '-', strtolower($property->title));
        $randomId = rand(1000000000, 9999999999);

        return $baseUrls[$platform] . $propertySlug . '-' . $randomId;
    }

    /**
     * Generate realistic social media post title based on platform and property
     */
    private function generateSocialTitle(string $platform, Property $property): string
    {
        $titles = [
            'instagram' => [
                "🏠 Stunning {$property->title} in {$property->location}! #RealEstate #PropertyForSale",
                "✨ Discover this beautiful property in {$property->location}. Perfect for families! #HomeSweetHome",
                "🌟 Luxury living awaits at {$property->title}. {$property->bedrooms} beds, {$property->bathrooms} baths! #LuxuryRealEstate",
                "🏡 Your dream home is here! {$property->title} - {$property->size}m² of pure comfort. #DreamHome",
            ],
            'tiktok' => [
                "Walking tour of {$property->title} in {$property->location}! 🏠 #RealEstate #PropertyTour",
                "Quick look at this amazing property! {$property->bedrooms} bedrooms, {$property->bathrooms} bathrooms! #HomeTour",
                "Property spotlight: {$property->title} - Perfect for your next chapter! ✨ #RealEstate",
                "Behind the scenes at {$property->title}! See why this property is special 🎥 #PropertyShowcase",
            ],
            'facebook' => [
                "For Sale: {$property->title} - {$property->bedrooms}BR/{$property->bathrooms}BA in {$property->location}",
                "Beautiful property available! {$property->title} - {$property->size}m² with amazing amenities",
                "Don't miss out on {$property->title}! Prime location in {$property->location} with {$property->bedrooms} bedrooms",
                "Property Alert: {$property->title} - Your perfect home awaits! Contact us for viewing",
            ],
            'twitter' => [
                "🏠 New listing: {$property->title} in {$property->location}! {$property->bedrooms}BR {$property->bathrooms}BA #RealEstate",
                "Property of the day: {$property->title} - {$property->size}m² of luxury living! #PropertyListing",
                "Looking for your dream home? Check out {$property->title} in {$property->location}! #HomeForSale",
                "Prime property alert! {$property->title} - Perfect location, amazing features! #RealEstate",
            ],
            'youtube' => [
                "Virtual Tour: {$property->title} - Complete Property Walkthrough",
                "Property Showcase: {$property->title} in {$property->location} - Full Tour",
                "Explore {$property->title} - {$property->bedrooms}BR {$property->bathrooms}BA Property Tour",
                "{$property->title} Property Tour - See Every Room and Feature!",
            ],
            'linkedin' => [
                "Professional property listing: {$property->title} - {$property->size}m² commercial space in {$property->location}",
                "Business opportunity: {$property->title} - Prime commercial property available",
                "Investment property: {$property->title} - {$property->bedrooms}BR residential property in {$property->location}",
                "Property market update: {$property->title} now available for viewing",
            ],
            'pinterest' => [
                "Beautiful {$property->title} - {$property->bedrooms}BR {$property->bathrooms}BA Home Inspiration",
                "Dream Home: {$property->title} in {$property->location} - Property Inspiration",
                "Home Decor Ideas: {$property->title} - {$property->size}m² of Stylish Living",
                "Property Goals: {$property->title} - Your Future Home Awaits",
            ],
        ];

        $platformTitles = $titles[$platform] ?? ["{$property->title} - Amazing Property in {$property->location}"];

        return $platformTitles[array_rand($platformTitles)];
    }
}
