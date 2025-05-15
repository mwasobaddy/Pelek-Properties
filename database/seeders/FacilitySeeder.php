<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run()
    {
        $facilities = [
            ['name' => 'Parking', 'description' => 'Available parking space', 'type' => 'commercial'],
            ['name' => 'Security', 'description' => '24/7 security service', 'type' => 'commercial'],
            ['name' => 'High-speed Internet', 'description' => 'Fiber optic internet connection', 'type' => 'commercial'],
            ['name' => 'Conference Room', 'description' => 'Meeting and conference facilities', 'type' => 'commercial'],
            ['name' => 'Elevator', 'description' => 'Passenger and cargo elevators', 'type' => 'commercial'],
            ['name' => 'CCTV', 'description' => 'Security camera system', 'type' => 'commercial'],
            ['name' => 'Air Conditioning', 'description' => 'Central air conditioning system', 'type' => 'commercial'],
            ['name' => 'Reception', 'description' => 'Front desk services', 'type' => 'commercial'],
            ['name' => 'Backup Generator', 'description' => 'Power backup system', 'type' => 'commercial'],
            ['name' => 'Kitchen', 'description' => 'Shared kitchen facilities', 'type' => 'commercial'],
        ];

        foreach ($facilities as $facility) {
            Facility::create($facility);
        }
    }
}
