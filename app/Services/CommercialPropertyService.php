<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Facility;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CommercialPropertyService
{
    public function __construct(
        private readonly PropertyService $propertyService
    ) {}

    /**
     * Get all commercial properties with optional filtering
     */
    public function getCommercialProperties(array $filters = []): LengthAwarePaginator
    {
        $query = Property::query()
            ->where('listing_type', 'commercial')
            ->with(['propertyType', 'featuredImage', 'images', 'facilities']);

        // Apply filters
        if (!empty($filters['commercial_type'])) {
            $query->where('commercial_type', $filters['commercial_type']);
        }

        if (!empty($filters['min_size'])) {
            $query->where('total_square_feet', '>=', $filters['min_size']);
        }

        if (!empty($filters['max_size'])) {
            $query->where('total_square_feet', '<=', $filters['max_size']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['has_parking'])) {
            $query->where('has_parking', true);
        }

        if (!empty($filters['facilities'])) {
            $query->whereHas('facilities', function ($q) use ($filters) {
                $q->whereIn('facilities.id', $filters['facilities']);
            });
        }

        return $query->latest()->paginate(12);
    }

    /**
     * Get featured commercial properties
     */
    public function getFeaturedCommercial(int $limit = 4): Collection
    {
        return Property::where('listing_type', 'commercial')
            ->where('is_featured', true)
            ->with(['propertyType', 'featuredImage', 'facilities'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get similar commercial properties
     */
    public function getSimilarProperties(Property $property, int $limit = 3): Collection
    {
        return Property::where('listing_type', 'commercial')
            ->where('id', '!=', $property->id)
            ->where('commercial_type', $property->commercial_type)
            ->whereBetween('total_square_feet', [
                $property->total_square_feet * 0.7,
                $property->total_square_feet * 1.3
            ])
            ->with(['propertyType', 'featuredImage', 'facilities'])
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }

    /**
     * Get all active facilities grouped by type
     */
    public function getFacilitiesGroupedByType(): Collection
    {
        return Facility::active()
            ->get()
            ->groupBy('type');
    }

    /**
     * Get commercial properties for admin management
     */
    public function getAdminCommercialProperties(): Collection
    {
        return Property::with(['propertyType', 'images', 'facilities', 'maintenanceRecords'])
            ->where('listing_type', 'commercial')
            ->latest()
            ->get();
    }

    /**
     * Update facility maintenance status
     */
    public function updateFacilityMaintenance(
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
     * Update commercial lease information
     */
    public function updateLeaseInfo(
        Property $property,
        array $leaseData
    ): void {
        $property->update([
            'lease_terms' => $leaseData['terms'],
            'lease_rate' => $leaseData['rate'],
            'lease_type' => $leaseData['type'], // net, gross, modified gross
            'lease_duration' => $leaseData['duration'],
            'is_available' => $leaseData['is_available'],
            'available_from' => $leaseData['available_from'],
            'minimum_lease_term' => $leaseData['minimum_term'],
        ]);
    }

    /**
     * Get facility analytics
     */
    public function getFacilityAnalytics(Property $property): array
    {
        $maintenanceRecords = $property->maintenanceRecords()
            ->whereYear('created_at', now()->year)
            ->get();

        return [
            'total_maintenance_cost' => $maintenanceRecords->sum('cost'),
            'pending_maintenance' => $maintenanceRecords->where('status', 'pending')->count(),
            'scheduled_maintenance' => $maintenanceRecords->where('status', 'scheduled')->count(),
            'completed_maintenance' => $maintenanceRecords->where('status', 'completed')->count(),
            'maintenance_by_priority' => $maintenanceRecords->groupBy('priority')
                ->map(fn($records) => $records->count()),
            'monthly_costs' => $maintenanceRecords->groupBy(fn($record) => $record->created_at->format('M'))
                ->map(fn($records) => $records->sum('cost')),
        ];
    }

    /**
     * Get lease analytics
     */
    public function getLeaseAnalytics(Property $property): array
    {
        $leaseHistory = $property->leaseHistory;
        $currentLease = $leaseHistory->first();

        return [
            'current_lease_status' => $currentLease ? 'leased' : 'available',
            'current_lease_expires' => $currentLease?->end_date,
            'average_lease_duration' => $leaseHistory->avg('duration_months'),
            'total_lease_revenue' => $leaseHistory->sum('monthly_rate') * $leaseHistory->avg('duration_months'),
            'occupancy_rate' => $this->calculateOccupancyRate($property),
        ];
    }

    /**
     * Calculate property occupancy rate
     */
    private function calculateOccupancyRate(Property $property): float
    {
        $leaseHistory = $property->leaseHistory()
            ->whereYear('start_date', now()->year)
            ->get();
        
        if ($leaseHistory->isEmpty()) {
            return 0;
        }

        $totalDays = 365;
        $occupiedDays = 0;

        foreach ($leaseHistory as $lease) {
            $startDate = Carbon::parse($lease->start_date);
            $endDate = Carbon::parse($lease->end_date);
            
            if ($startDate->year < now()->year) {
                $startDate = Carbon::createFromDate(now()->year, 1, 1);
            }
            
            if ($endDate->year > now()->year) {
                $endDate = Carbon::createFromDate(now()->year, 12, 31);
            }
            
            $occupiedDays += $startDate->diffInDays($endDate);
        }

        return ($occupiedDays / $totalDays) * 100;
    }
}
