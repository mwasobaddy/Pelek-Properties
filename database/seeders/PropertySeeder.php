<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Amenity;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        // Get the admin user
        $admin = User::where('email', 'admin@pelekproperties.com')->firstOrFail();

        // Get all property types (they should exist from PropertyTypeSeeder)
        $propertyTypes = PropertyType::all();

        // Create properties with different listing types
        $createProperty = function ($count, $type) use ($propertyTypes, $admin) {
            return collect(range(1, $count))->map(function () use ($type, $propertyTypes, $admin) {
                // Calculate size and determine square range
                $size = rand(30, 1000);
                $squareRange = match(true) {
                    $size <= 50 => '0-50',
                    $size <= 100 => '50-100',
                    $size <= 200 => '100-200',
                    $size <= 300 => '200-300',
                    $size <= 500 => '300-500',
                    default => '500+'
                };

                return Property::factory()
                    ->$type()
                    ->create([
                        'user_id' => $admin->id,
                        'property_type_id' => $propertyTypes->random()->id,
                        'size' => $size,
                        'square_range' => $squareRange,
                        'floors' => rand(1, 20), // Random number of floors between 1 and 20
                    ]);
            });
        };

        $properties = collect([
            // 20 properties for sale
            $createProperty(20, 'forSale'),
            // 20 properties for rent
            $createProperty(20, 'forRent'),
            // 20 properties for Airbnb
            $createProperty(20, 'forAirbnb'),
            // 20 commercial properties
            $createProperty(20, 'forCommercial'),
        ])->flatten();

        // For each property
        $properties->each(function ($property) {
            // Add 3-6 images per property
            PropertyImage::factory(rand(3, 6))->create([
                'property_id' => $property->id,
            ]);

            // Ensure one featured image
            PropertyImage::factory()->featured()->create([
                'property_id' => $property->id,
            ]);

            // Attach 3-6 random amenities
            $propertyAmenities = Amenity::inRandomOrder()
                ->limit(rand(3, 6))
                ->get()
                ->pluck('id')
                ->toArray();
            
            // Using sync instead of attach and specifying the exact pivot table name
            $property->amenities()
                ->sync($propertyAmenities);
        });

        // Make some properties featured
        Property::inRandomOrder()
            ->limit(5)
            ->get()
            ->each(fn($property) => $property->update(['is_featured' => true]));
    }
}
