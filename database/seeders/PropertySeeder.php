<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Amenity;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        // Get admin users with error checking
        $admins = User::whereIn('email', [
            'admin@pelekproperties.co.ke',
            'pelekproperties2025@gmail.com'
        ])->get();

        if ($admins->isEmpty()) {
            throw new \RuntimeException('No admin users found for property creation');
        }

        // Cache property types
        $propertyTypes = PropertyType::all();
        if ($propertyTypes->isEmpty()) {
            throw new \RuntimeException('No property types found. Please run PropertyTypeSeeder first.');
        }

        // Cache amenities for reuse
        $allAmenities = Amenity::all();
        if ($allAmenities->isEmpty()) {
            throw new \RuntimeException('No amenities found. Please run AmenitySeeder first.');
        }

        $createProperty = function ($count, $type) use ($propertyTypes, $admins) {
            return collect(range(1, $count))->map(function () use ($type, $propertyTypes, $admins) {
                $size = rand(30, 1000);
                $squareRange = $this->calculateSquareRange($size);

                return Property::factory()
                    ->$type()
                    ->create([
                        'user_id' => $admins->random()->id,
                        'property_type_id' => $propertyTypes->random()->id,
                        'size' => $size,
                        'square_range' => $squareRange,
                        'floors' => rand(1, 20),
                    ]);
            });
        };

        DB::beginTransaction();
        try {
            // Create properties in chunks
            $propertyTypes = ['forSale', 'forRent', 'forAirbnb', 'forCommercial'];
            foreach ($propertyTypes as $type) {
                $properties = $createProperty(20, $type);
                $this->attachPropertiesData($properties, $allAmenities);
            }

            // Feature random properties
            Property::inRandomOrder()
                ->limit(5)
                ->update(['is_featured' => true]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function calculateSquareRange(int $size): string
    {
        return match(true) {
            $size <= 50 => '0-50',
            $size <= 100 => '50-100',
            $size <= 200 => '100-200',
            $size <= 300 => '200-300',
            $size <= 500 => '300-500',
            default => '500+'
        };
    }

    private function attachPropertiesData($properties, $amenities): void
    {
        $properties->each(function ($property) use ($amenities) {
            // Batch create images
            $images = collect([
                ...PropertyImage::factory(rand(3, 6))->make([
                    'property_id' => $property->id,
                ]),
                PropertyImage::factory()->featured()->make([
                    'property_id' => $property->id,
                ])
            ]);
            PropertyImage::insert($images->toArray());

            // Attach amenities
            $property->amenities()->sync(
                $amenities->random(rand(3, 6))->pluck('id')
            );
        });
    }
}
