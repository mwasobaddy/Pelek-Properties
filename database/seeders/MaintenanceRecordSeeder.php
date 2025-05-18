<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaintenanceRecord;
use App\Models\Property;
use Carbon\Carbon;

class MaintenanceRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all property IDs
        $propertyIds = Property::pluck('id')->toArray();
        
        if (empty($propertyIds)) {
            // Create a few properties if none exist
            Property::factory(5)->create();
            $propertyIds = Property::pluck('id')->toArray();
        }

        $issueTypes = [
            'Plumbing',
            'Electrical',
            'HVAC',
            'Structural',
            'Appliance',
            'Pest Control',
            'Landscaping',
            'Security System',
            'Painting',
            'General Repairs'
        ];

        $descriptions = [
            'Water leak under kitchen sink',
            'No power in master bedroom',
            'AC not cooling properly',
            'Crack in living room wall',
            'Dishwasher not draining',
            'Termite infestation signs',
            'Overgrown vegetation in backyard',
            'Front door lock malfunction',
            'Peeling paint in bathroom',
            'Broken window handle'
        ];

        $requestedBy = [
            'John Tenant',
            'Mary Resident',
            'Property Manager',
            'Building Inspector',
            'Maintenance Staff'
        ];

        // Create 50 maintenance records
        for ($i = 0; $i < 50; $i++) {
            $createdAt = Carbon::now()->subDays(rand(1, 90));
            $status = fake()->randomElement(['pending', 'scheduled', 'in_progress', 'completed', 'cancelled']);
            
            $record = [
                'property_id' => fake()->randomElement($propertyIds),
                'issue_type' => fake()->randomElement($issueTypes),
                'description' => fake()->randomElement($descriptions),
                'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
                'requested_by' => fake()->randomElement($requestedBy),
                'status' => $status,
                'scheduled_date' => $status !== 'pending' ? $createdAt->copy()->addDays(rand(1, 7)) : null,
                'completed_date' => $status === 'completed' ? $createdAt->copy()->addDays(rand(8, 14)) : null,
                'cost' => $status === 'completed' ? fake()->randomFloat(2, 100, 5000) : null,
                'notes' => fake()->boolean(70) ? fake()->sentence(rand(3, 8)) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(rand(0, 5))
            ];
            
            MaintenanceRecord::create($record);
        }
    }
}
