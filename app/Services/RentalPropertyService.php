<?php

namespace App\Services;

use App\Models\Property;
use App\Models\MaintenanceRecord;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RentalPropertyService
{
    public function __construct(
        private readonly PropertyService $propertyService
    ) {}

    /**
     * Get all rental properties with optional filtering
     */
    public function getRentalProperties(array $filters = []): LengthAwarePaginator
    {
        $query = Property::query()
            ->where('listing_type', 'rent')
            ->with(['propertyType', 'featuredImage', 'images', 'amenities']);

        // Apply filters
        if (!empty($filters['price_min'])) {
            $query->where('rental_price_monthly', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('rental_price_monthly', '<=', $filters['price_max']);
        }

        if (!empty($filters['furnished'])) {
            $query->where('is_furnished', true);
        }

        if (!empty($filters['available_from'])) {
            $query->where('available_from', '<=', $filters['available_from']);
        }

        if (!empty($filters['min_lease'])) {
            $query->where('minimum_lease_period', '>=', $filters['min_lease']);
        }

        if (!empty($filters['property_type'])) {
            $query->where('property_type_id', $filters['property_type']);
        }

        return $query->latest()->paginate(12);
    }

    /**
     * Get rental properties for admin management
     */
    public function getAdminRentalProperties(): Collection
    {
        return Property::with(['propertyType', 'images', 'tenantInfo', 'maintenanceRecords'])
            ->where('listing_type', 'rent')
            ->latest()
            ->get();
    }

    /**
     * Update property availability status
     */
    public function updateAvailabilityStatus(Property $property, bool $isAvailable): void
    {
        $property->update([
            'is_available' => $isAvailable,
            'available_from' => $isAvailable ? now() : null,
        ]);
    }

    /**
     * Track tenant information
     */
    public function updateTenantInfo(
        Property $property,
        array $tenantData
    ): void {
        $property->tenantInfo()->updateOrCreate(
            ['property_id' => $property->id],
            [
                'tenant_name' => $tenantData['name'],
                'tenant_phone' => $tenantData['phone'],
                'tenant_email' => $tenantData['email'],
                'lease_start' => $tenantData['lease_start'],
                'lease_end' => $tenantData['lease_end'],
                'monthly_rent' => $tenantData['monthly_rent'],
                'security_deposit' => $tenantData['security_deposit'],
                'payment_status' => $tenantData['payment_status'] ?? 'pending',
                'notes' => $tenantData['notes'] ?? null,
            ]
        );
    }

    /**
     * Record maintenance request
     */
    public function recordMaintenanceRequest(
        Property $property,
        array $maintenanceData
    ): void {
        $property->maintenanceRecords()->create([
            'issue_type' => $maintenanceData['issue_type'],
            'description' => $maintenanceData['description'],
            'priority' => $maintenanceData['priority'],
            'requested_by' => $maintenanceData['requested_by'],
            'status' => $maintenanceData['status'] ?? 'pending',
            'scheduled_date' => $maintenanceData['scheduled_date'] ?? null,
            'completed_date' => $maintenanceData['completed_date'] ?? null,
            'cost' => $maintenanceData['cost'] ?? null,
            'notes' => $maintenanceData['notes'] ?? null,
        ]);
    }

    /**
     * Update maintenance record status
     */
    public function updateMaintenanceStatus(
        int $maintenanceId,
        string $status,
        ?string $completedDate = null,
        ?float $cost = null
    ): void {
        $record = MaintenanceRecord::findOrFail($maintenanceId);
        $record->update([
            'status' => $status,
            'completed_date' => $completedDate,
            'cost' => $cost,
        ]);
    }

    /**
     * Get property analytics
     */
    public function getPropertyAnalytics(Property $property): array
    {
        $tenantInfo = $property->tenantInfo;
        $maintenanceRecords = $property->maintenanceRecords;

        return [
            'occupancy_rate' => $this->calculateOccupancyRate($property),
            'total_maintenance_cost' => $maintenanceRecords->sum('cost'),
            'pending_maintenance' => $maintenanceRecords->where('status', 'pending')->count(),
            'current_tenant' => $tenantInfo ? [
                'name' => $tenantInfo->tenant_name,
                'lease_remaining' => Carbon::parse($tenantInfo->lease_end)->diffInDays(now()),
                'payment_status' => $tenantInfo->payment_status,
            ] : null,
        ];
    }

    /**
     * Calculate property occupancy rate
     */
    private function calculateOccupancyRate(Property $property): float
    {
        $tenantHistory = $property->tenantInfo()->withTrashed()->get();
        
        if ($tenantHistory->isEmpty()) {
            return 0;
        }

        $totalDays = 0;
        $occupiedDays = 0;

        foreach ($tenantHistory as $tenant) {
            $leaseStart = Carbon::parse($tenant->lease_start);
            $leaseEnd = Carbon::parse($tenant->lease_end);
            
            $totalDays += $leaseStart->diffInDays($leaseEnd);
            if ($tenant->deleted_at) {
                $occupiedDays += $leaseStart->diffInDays($tenant->deleted_at);
            } else {
                $occupiedDays += $leaseStart->diffInDays(now());
            }
        }

        return $totalDays > 0 ? ($occupiedDays / $totalDays) * 100 : 0;
    }

    /**
     * Get featured rental properties
     */
    public function getFeaturedRentals(int $limit = 4): Collection
    {
        return Property::where('listing_type', 'rent')
            ->where('is_featured', true)
            ->with(['propertyType', 'featuredImage'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Check rental property availability for a given date
     */
    public function isAvailableForDate(Property $property, string $date): bool
    {
        if (!$property->available_from) {
            return false;
        }

        return $property->available_from <= $date;
    }

    /**
     * Get similar rental properties
     */
    public function getSimilarProperties(Property $property, int $limit = 3): Collection
    {
        return Property::where('listing_type', 'rent')
            ->where('id', '!=', $property->id)
            ->where('property_type_id', $property->property_type_id)
            ->whereBetween('rental_price_monthly', [
                $property->rental_price_monthly * 0.8,
                $property->rental_price_monthly * 1.2
            ])
            ->with(['propertyType', 'featuredImage'])
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }
}
