<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Property Management Permissions
        $propertyPermissions = [
            'list_properties',
            'create_property',
            'edit_property',
            'delete_property',
            'feature_property',
            'manage_rental_properties',
            'manage_sale_properties',
            'manage_airbnb_properties',
        ];

        // Content Management Permissions
        $contentPermissions = [
            'manage_blog',
            'create_post',
            'edit_post',
            'delete_post',
            'manage_pages',
            'manage_media',
        ];

        // Service Management Permissions
        $servicePermissions = [
            'manage_services',
            'manage_bookings',
            'manage_inquiries',
            'respond_inquiries',
        ];

        // User Management Permissions
        $userPermissions = [
            'manage_users',
            'create_user',
            'edit_user',
            'delete_user',
            'manage_roles',
        ];

        // System Permissions
        $systemPermissions = [
            'access_dashboard',
            'manage_settings',
            'view_reports',
            'manage_integrations',
        ];

        // Create Permissions
        $allPermissions = array_merge(
            $propertyPermissions,
            $contentPermissions,
            $servicePermissions,
            $userPermissions,
            $systemPermissions
        );

        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Roles and Assign Permissions
        
        // Admin Role (has all permissions)
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Property Manager Role
        $propertyManagerRole = Role::create(['name' => 'property_manager']);
        $propertyManagerRole->givePermissionTo([
            'list_properties',
            'create_property',
            'edit_property',
            'manage_rental_properties',
            'manage_sale_properties',
            'manage_airbnb_properties',
            'manage_bookings',
            'manage_inquiries',
            'respond_inquiries',
            'access_dashboard',
            'view_reports',
        ]);

        // Content Editor Role
        $contentEditorRole = Role::create(['name' => 'content_editor']);
        $contentEditorRole->givePermissionTo([
            'manage_blog',
            'create_post',
            'edit_post',
            'manage_pages',
            'manage_media',
            'access_dashboard',
        ]);

        // Service Manager Role
        $serviceManagerRole = Role::create(['name' => 'service_manager']);
        $serviceManagerRole->givePermissionTo([
            'manage_services',
            'manage_bookings',
            'manage_inquiries',
            'respond_inquiries',
            'access_dashboard',
            'view_reports',
        ]);
    }
}
