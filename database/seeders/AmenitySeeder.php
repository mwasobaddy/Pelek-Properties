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
                'Parking' => 'clipboard-document-list',
                'Air Conditioning' => 'clipboard-document-list',
                'Heating' => 'clipboard-document-list',
                'Internet' => 'clipboard-document-list',
                'Cable TV' => 'clipboard-document-list',
            ],
            'luxury' => [
                'Swimming Pool' => 'clipboard-document-list',
                'Gym' => 'clipboard-document-list',
                'Sauna' => 'clipboard-document-list',
                'Tennis Court' => 'clipboard-document-list',
                'Cinema Room' => 'clipboard-document-list',
            ],
            'outdoor' => [
                'Garden' => 'clipboard-document-list',
                'Balcony' => 'clipboard-document-list',
                'BBQ' => 'clipboard-document-list',
                'Terrace' => 'clipboard-document-list',
                'Private Beach' => 'clipboard-document-list',
            ],
            'security' => [
                '24/7 Security' => 'clipboard-document-list',
                'CCTV' => 'clipboard-document-list',
                'Gated Community' => 'clipboard-document-list',
                'Intercom' => 'clipboard-document-list',
                'Security Alarm' => 'clipboard-document-list',
            ],
            'utility' => [
                'Laundry' => 'clipboard-document-list',
                'Storage' => 'clipboard-document-list',
                'Elevator' => 'clipboard-document-list',
                'Generator' => 'clipboard-document-list',
                'Water Tank' => 'clipboard-document-list',
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
