<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user if not exists
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::role('admin')->first() ?? User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Create 15 blog posts
        BlogPost::factory()
            ->count(12)
            ->published()
            ->state(['author_id' => $admin->id])
            ->create();

        // Create 3 featured posts
        BlogPost::factory()
            ->count(3)
            ->published()
            ->featured()
            ->state(['author_id' => $admin->id])
            ->create();
    }
}
