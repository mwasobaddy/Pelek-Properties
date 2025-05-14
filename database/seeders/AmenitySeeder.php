<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            'basic' => [
                'Parking' => 'car',
                'Air Conditioning' => 'snowflake',
                'Heating' => 'fire',
                'Internet' => 'wifi',
                'Cable TV' => 'tv',
            ],
            'luxury' => [
                'Swimming Pool' => 'water',
                'Gym' => 'dumbbell',
                'Sauna' => 'hot-tub',
                'Tennis Court' => 'tennis',
                'Cinema Room' => 'film',
            ],
            'outdoor' => [
                'Garden' => 'tree',
                'Balcony' => 'door-open',
                'BBQ' => 'fire',
                'Terrace' => 'sun',
                'Private Beach' => 'umbrella-beach',
            ],
            'security' => [
                '24/7 Security' => 'shield',
                'CCTV' => 'camera',
                'Gated Community' => 'lock',
                'Intercom' => 'phone',
                'Security Alarm' => 'bell',
            ],
            'utility' => [
                'Laundry' => 'washing-machine',
                'Storage' => 'box',
                'Elevator' => 'arrow-up',
                'Generator' => 'bolt',
                'Water Tank' => 'water',
            ],
        ];

        foreach ($amenities as $category => $items) {
            foreach ($items as $name => $icon) {
                Amenity::create([
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'icon' => $icon,
                    'category' => $category,
                ]);
            }
        }
    }
}
