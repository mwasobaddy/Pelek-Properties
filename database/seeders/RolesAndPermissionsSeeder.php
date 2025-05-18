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
            'view_management_contract',
            'create_management_contract',
            'edit_management_contract',
            'terminate_management_contract',
            'record_financial_transaction',
            'view_financial_records',
            'manage_maintenance_records',
            'edit_maintenance_record',
            'delete_maintenance_record',
            'manage_property_images',
            'manage_property_amenities',
            'manage_property_facilities',
            'manage_property_availability',
            'manage_property_offers',
            'create_property_offer',
            'edit_property_offer',
            'delete_property_offer',
            'accept_property_offer',
            'reject_property_offer',
            'manage_availability_calendar',
            'set_property_availability',
            'set_custom_pricing',
            'manage_sale_listings',
            'manage_property_documents',
            'manage_property_units',
            'view_property_analytics',
            'manage_development_projects',
        ];

        // Valuation Permissions
        $valuationPermissions = [
            'create_valuation_request',
            'edit_valuation_request',
            'view_valuation_request',
            'create_valuation_report',
            'edit_valuation_report',
            'view_valuation_report',
            'manage_market_analysis',
            'delete_valuation_report',
            'generate_market_analysis',
            'edit_market_analysis',
            'delete_market_analysis',
            'export_valuation_report',
            'bulk_valuations',
        ];

        // Booking Permissions
        $bookingPermissions = [
            'create_booking',
            'edit_booking',
            'cancel_booking',
            'view_bookings',
            'manage_viewing_appointments',
            'approve_viewing_requests',
            'manage_property_bookings',
            'create_property_booking',
            'edit_property_booking',
            'delete_property_booking',
            'view_booking_calendar',
            'export_booking_reports',
            'manage_guest_information',
        ];

        // Tenant Permissions
        $tenantPermissions = [
            'manage_tenants',
            'create_tenant',
            'edit_tenant',
            'delete_tenant',
            'view_tenant_history',
        ];

        // Content Management Permissions
        $contentPermissions = [
            'manage_blog',
            'create_post',
            'edit_post',
            'delete_post',
            'manage_pages',
            'manage_media',
            'publish_posts',
            'feature_posts',
            'manage_post_categories',
            'manage_post_tags',
            'moderate_comments',
            'export_blog_statistics',
        ];

        // Service Management Permissions
        $servicePermissions = [
            'manage_services',
            'manage_bookings',
            'manage_inquiries',
            'respond_inquiries',
            'create_maintenance_request',
            'edit_maintenance_request',
            'update_maintenance_status',
            'assign_maintenance_task',
            'schedule_maintenance',
            'view_maintenance_history',
            'manage_service_providers',
            'generate_service_reports',
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

        // Commercial Property Permissions
        $commercialPermissions = [
            'manage_commercial_properties',
            'create_commercial_lease',
            'edit_commercial_lease',
            'terminate_commercial_lease',
            'manage_lease_renewals',
            'manage_lease_assignments',
            'view_lease_history',
        ];

        // Document Management Permissions
        $documentPermissions = [
            'manage_documents',
            'upload_documents',
            'delete_documents',
            'share_documents',
            'manage_document_templates',
        ];

        // Analytics & Reporting Permissions
        $analyticsPermissions = [
            'view_analytics_dashboard',
            'generate_performance_reports',
            'export_reports',
            'manage_custom_reports',
            'view_market_trends',
        ];

        // Sales Permissions
        $salesPermissions = [
            'manage_property_sales',
            'create_sale_listing',
            'edit_sale_listing',
            'delete_sale_listing',
            'manage_mortgages',
            'manage_title_deeds',
            'manage_development_sales',
            'view_sales_analytics',
        ];

        // Facility Management Permissions
        $facilityPermissions = [
            'manage_facilities',
            'create_facility',
            'edit_facility',
            'delete_facility',
            'view_facility_specs',
            'manage_facility_status',
        ];

        // Reporting & Export Permissions
        $reportingPermissions = [
            'generate_tenant_reports',
            'generate_financial_reports',
            'generate_occupancy_reports',
            'export_property_data',
            'export_maintenance_reports',
            'view_audit_logs',
        ];

        // Create Permissions
        $allPermissions = array_merge(
            $propertyPermissions,
            $valuationPermissions,
            $bookingPermissions,
            $tenantPermissions,
            $contentPermissions,
            $servicePermissions,
            $userPermissions,
            $systemPermissions,
            $commercialPermissions,
            $documentPermissions,
            $analyticsPermissions,
            $salesPermissions,
            $facilityPermissions,
            $reportingPermissions
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
            'manage_property_bookings',
            'create_property_booking',
            'edit_property_booking',
            'view_booking_calendar',
            'manage_guest_information',
            'manage_property_images',
            'manage_property_amenities',
            'manage_property_facilities',
            'manage_maintenance_records',
            'record_financial_transaction',
            'view_financial_records',
            'manage_facilities',
            'view_facility_specs',
            'manage_facility_status',
            'generate_occupancy_reports',
            'export_property_data',
            'manage_property_offers',
            'view_property_analytics',
            'generate_market_analysis',
            'view_market_trends'
        ]);

        // Facility Manager Role
        $facilityManagerRole = Role::create(['name' => 'facility_manager']);
        $facilityManagerRole->givePermissionTo([
            'manage_facilities',
            'create_facility',
            'edit_facility',
            'delete_facility',
            'view_facility_specs',
            'manage_facility_status',
            'manage_maintenance_records',
            'access_dashboard',
            'view_reports'
        ]);

        // Sales Manager Role
        $salesManagerRole = Role::create(['name' => 'sales_manager']);
        $salesManagerRole->givePermissionTo([
            'manage_property_sales',
            'create_sale_listing',
            'edit_sale_listing',
            'delete_sale_listing',
            'manage_mortgages',
            'manage_title_deeds',
            'manage_development_sales',
            'view_sales_analytics',
            'manage_property_offers',
            'access_dashboard',
            'view_reports',
            'generate_market_analysis',
            'view_market_trends'
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
            'create_maintenance_request',
            'edit_maintenance_request',
            'update_maintenance_status',
            'assign_maintenance_task',
            'schedule_maintenance',
            'view_maintenance_history',
            'manage_service_providers',
            'generate_service_reports',
        ]);
    }
}
