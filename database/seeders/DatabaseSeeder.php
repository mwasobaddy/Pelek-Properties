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
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // Create admin user
        $admin = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@pelekproperties.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $this->call([
            PropertyTypeSeeder::class,
            AmenitySeeder::class,
            FacilitySeeder::class,
            PropertySeeder::class,
            CommercialPropertySeeder::class,
        ]);
    }
}
