<?php

namespace Database\Seeders;

use App\Models\ManagementContract;
use App\Models\FinancialRecord;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertyManagementSeeder extends Seeder
{
    public function run(): void
    {
        // Create the admin user if it doesn't exist yet
        $admin = User::firstOrCreate(
            ['email' => 'admin@pelekproperties.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create contracts for existing properties
        Property::all()->each(function ($property) use ($admin) {
            // Create active management contract
            ManagementContract::factory()->create([
                'property_id' => $property->id,
                'admin_id' => $admin->id,
            ]);

            // Create some expired contracts
            ManagementContract::factory()
                ->expired()
                ->count(2)
                ->create([
                    'property_id' => $property->id,
                    'admin_id' => $admin->id,
                ]);

            // Create financial records for the last year
            $startDate = now()->subYear();
            $endDate = now();

            // Create monthly rent income records
            while ($startDate <= $endDate) {
                FinancialRecord::factory()
                    ->income()
                    ->create([
                        'property_id' => $property->id,
                        'category' => 'rent',
                        'amount' => $property->rental_price_monthly ?? 50000,
                        'transaction_date' => $startDate->copy(),
                        'recorded_by' => $admin->id,
                        'status' => 'completed',
                    ]);

                // Create some random expenses
                FinancialRecord::factory()
                    ->expense()
                    ->count(rand(1, 3))
                    ->create([
                        'property_id' => $property->id,
                        'recorded_by' => $admin->id,
                        'transaction_date' => $startDate->copy()->addDays(rand(1, 28)),
                    ]);

                $startDate->addMonth();
            }

            // Create some pending transactions
            FinancialRecord::factory()
                ->pending()
                ->count(3)
                ->create([
                    'property_id' => $property->id,
                    'recorded_by' => $admin->id,
                    'transaction_date' => now()->addDays(rand(1, 30)),
                ]);
        });
    }
}
