<?php

namespace Database\Seeders;

use App\Models\ManagementContract;
use App\Models\FinancialRecord;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class PropertyManagementSeeder extends Seeder
{
    private const ADMIN_USERS = [
        [
            'email' => 'admin@pelekproperties.co.ke',
            'name' => 'Admin User',
            'password' => 'Pelek@2025',
        ],
        [
            'email' => 'pelekproperties2025@gmail.com',
            'name' => 'Admin User',
            'password' => 'Pelek@2025',
        ],
    ];

    public function run(): void
    {
        try {
            $admin = $this->createAdminUsers();
            $this->seedPropertyData($admin);
        } catch (\Exception $e) {
            \Log::error('Error seeding property management data: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createAdminUsers(): User
    {
        $firstAdmin = null;
        
        foreach (self::ADMIN_USERS as $adminData) {
            $admin = User::firstOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'password' => Hash::make($adminData['password']),
                ]
            );
            
            $firstAdmin = $firstAdmin ?? $admin;
        }

        return $firstAdmin;
    }

    private function seedPropertyData(User $admin): void
    {
        Property::chunk(100, function ($properties) use ($admin) {
            foreach ($properties as $property) {
                $this->createManagementContracts($property, $admin);
                $this->createFinancialRecords($property, $admin);
            }
        });
    }

    private function createManagementContracts(Property $property, User $admin): void
    {
        // Active contract
        ManagementContract::factory()->create([
            'property_id' => $property->id,
            'admin_id' => $admin->id,
        ]);

        // Expired contracts
        ManagementContract::factory()
            ->expired()
            ->count(2)
            ->create([
                'property_id' => $property->id,
                'admin_id' => $admin->id,
            ]);
    }

    private function createFinancialRecords(Property $property, User $admin): void
    {
        $this->createHistoricalRecords($property, $admin);
        $this->createPendingTransactions($property, $admin);
    }

    private function createHistoricalRecords(Property $property, User $admin): void
    {
        $startDate = now()->subYear();
        $endDate = now();

        while ($startDate <= $endDate) {
            $this->createMonthlyRecords($property, $admin, $startDate);
            $startDate->addMonth();
        }
    }

    private function createMonthlyRecords(Property $property, User $admin, Carbon $date): void
    {
        // Monthly rent income
        FinancialRecord::factory()->income()->create([
            'property_id' => $property->id,
            'category' => 'rent',
            'amount' => $property->rental_price_monthly ?? 50000,
            'transaction_date' => $date->copy(),
            'recorded_by' => $admin->id,
            'status' => 'completed',
        ]);

        // Random monthly expenses
        FinancialRecord::factory()
            ->expense()
            ->count(rand(1, 3))
            ->create([
                'property_id' => $property->id,
                'recorded_by' => $admin->id,
                'transaction_date' => $date->copy()->addDays(rand(1, 28)),
            ]);
    }

    private function createPendingTransactions(Property $property, User $admin): void
    {
        FinancialRecord::factory()
            ->pending()
            ->count(3)
            ->create([
                'property_id' => $property->id,
                'recorded_by' => $admin->id,
                'transaction_date' => now()->addDays(rand(1, 30)),
            ]);
    }
}
