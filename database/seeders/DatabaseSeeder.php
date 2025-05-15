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
        // Create default admin user first
        $admin = User::firstOrCreate(
            ['email' => 'admin@pelekproperties.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Make sure admin has the admin role
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);
        $admin->assignRole('admin');

        // Now run the rest of the seeders
        $this->call([
            PropertyTypeSeeder::class,
            AmenitySeeder::class,
            PropertySeeder::class,
            AvailabilityCalendarSeeder::class,
            PropertyManagementSeeder::class,
        ]);
    }
}
