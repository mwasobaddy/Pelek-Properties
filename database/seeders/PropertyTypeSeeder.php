<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class PropertyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Commercial',
                'slug' => 'commercial',
                'description' => 'Commercial properties like offices, retail spaces, and warehouses'
            ],
            [
                'name' => 'Residential',
                'slug' => 'residential',
                'description' => 'Residential properties for rent or sale'
            ],
            [
                'name' => 'Land',
                'slug' => 'land',
                'description' => 'Land plots for sale'
            ],
            [
                'name' => 'Vacation Rental',
                'slug' => 'vacation-rental',
                'description' => 'Short-term vacation rentals'
            ],
        ];

        foreach ($types as $type) {
            PropertyType::create($type);
        }
    }
}
