<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Facility;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class CommercialPropertySeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('email', 'admin@pelekproperties.com')->first();
        $commercialType = PropertyType::where('name', 'Commercial')->first();

        $commercialProperties = [
            [
                'title' => 'Modern Office Space in Business District',
                'slug' => 'modern-office-space-in-business-district',
                'description' => 'Prime office space in the heart of the business district',
                'type' => 'commercial',
                'listing_type' => 'rent',
                'commercial_type' => 'office',
                'price' => 250000,
                'size' => 2500,
                'location' => 'Business District',
                'city' => 'Nairobi',
                'address' => '123 Business Avenue',
                'available' => true,
                'floors' => 1,
                'maintenance_status' => 'excellent',
                'last_renovation' => '2023-01-15',
                'user_id' => $admin->id,
                'property_type_id' => $commercialType->id,
                'whatsapp_number' => '+254700000001',
            ],
            [
                'title' => 'Retail Space in Shopping Complex',
                'slug' => 'retail-space-in-shopping-complex',
                'description' => 'Strategic retail space in high-traffic shopping area',
                'type' => 'commercial',
                'listing_type' => 'rent',
                'commercial_type' => 'retail',
                'price' => 180000,
                'size' => 1500,
                'location' => 'Shopping District',
                'city' => 'Nairobi',
                'address' => '456 Retail Street',
                'available' => true,
                'floors' => 1,
                'maintenance_status' => 'good',
                'last_renovation' => '2022-08-20',
                'user_id' => $admin->id,
                'property_type_id' => $commercialType->id,
                'whatsapp_number' => '+254700000002',
            ],
            [
                'title' => 'Warehouse with Loading Dock',
                'slug' => 'warehouse-with-loading-dock',
                'description' => 'Spacious warehouse with modern loading facilities',
                'type' => 'commercial',
                'listing_type' => 'sale',
                'commercial_type' => 'warehouse',
                'price' => 350000,
                'size' => 5000,
                'location' => 'Industrial Area',
                'city' => 'Nairobi',
                'address' => '789 Industrial Road',
                'available' => true,
                'floors' => 1,
                'maintenance_status' => 'good',
                'last_renovation' => '2022-05-10',
                'user_id' => $admin->id,
                'property_type_id' => $commercialType->id,
                'whatsapp_number' => '+254700000003',
            ],
        ];

        foreach ($commercialProperties as $property) {
            $newProperty = Property::create($property);
            
            // Attach relevant facilities based on property type
            $facilities = Facility::where('type', 'commercial')->get();
            $facilityIds = [];
            
            foreach ($facilities as $facility) {
                // Customize facility assignment based on property type
                if ($property['commercial_type'] === 'office' && in_array($facility->name, ['Parking', 'Security', 'High-speed Internet', 'Conference Room', 'CCTV'])) {
                    $facilityIds[] = $facility->id;
                } elseif ($property['commercial_type'] === 'retail' && in_array($facility->name, ['Parking', 'Security', 'Air Conditioning', 'CCTV'])) {
                    $facilityIds[] = $facility->id;
                } elseif ($property['commercial_type'] === 'warehouse' && in_array($facility->name, ['Parking', 'Security', 'CCTV'])) {
                    $facilityIds[] = $facility->id;
                }
            }
            
            $newProperty->facilities()->attach($facilityIds);
        }
    }
}
