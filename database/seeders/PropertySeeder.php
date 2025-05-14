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
        // Create an admin user if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );

        // Get all property types (they should exist from PropertyTypeSeeder)
        $propertyTypes = PropertyType::all();

        // Create properties with different listing types
        $createProperty = function ($count, $type) use ($propertyTypes, $admin) {
            return collect(range(1, $count))->map(function () use ($type, $propertyTypes, $admin) {
                return Property::factory()
                    ->$type()
                    ->create([
                        'user_id' => $admin->id,
                        'property_type_id' => $propertyTypes->random()->id,
                    ]);
            });
        };

        $properties = collect([
            // 15 properties for sale
            $createProperty(15, 'forSale'),
            // 10 properties for rent
            $createProperty(10, 'forRent'),
            // 5 properties for Airbnb
            $createProperty(5, 'forAirbnb'),
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
