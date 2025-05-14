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

        // Create default admin user
        $user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@pelekproperties.com',
            'password' => bcrypt('password'),
        ]);

        $user->assignRole('admin');
    }
}
