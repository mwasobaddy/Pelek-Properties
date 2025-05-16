<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin users data
        $adminUsers = [
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

        // Create admin users
        foreach ($adminUsers as $userData) {
            $admin = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt($userData['password']),
                    'email_verified_at' => now(),
                ]
            );
            
            // Make sure each admin has the admin role
            $admin->assignRole('admin');
        }

        // Define all seeders to run
        $seeders = [
            RolesAndPermissionsSeeder::class,
            PropertyTypeSeeder::class,
            AmenitySeeder::class,
            PropertySeeder::class,
            AvailabilityCalendarSeeder::class,
            PropertyManagementSeeder::class,
        ];

        // Run all seeders in sequence
        foreach ($seeders as $seeder) {
            $this->call($seeder);
        }
    }
}
