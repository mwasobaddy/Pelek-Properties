<?php

namespace App\Services;

use App\Models\Property;
use App\Models\MaintenanceRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MaintenanceService
{
    /**
     * Get maintenance statistics for a collection of properties
     */
    public function getMaintenanceStatistics(Collection $properties): array
    {
        $records = MaintenanceRecord::whereIn('property_id', $properties->pluck('id'))->get();

        return [
            'total_requests' => $records->count(),
            'pending_requests' => $records->where('status', 'pending')->count(),
            'completed_requests' => $records->where('status', 'completed')->count(),
            'urgent_requests' => $records->where('priority', 'urgent')
                                    ->where('status', '!=', 'completed')
                                    ->count(),
            'total_cost' => $records->where('status', 'completed')->sum('cost'),
            'average_completion_time' => $this->calculateAverageCompletionTime($records),
        ];
    }

    /**
     * Record a maintenance request
     */
    public function recordMaintenanceRequest(
        Property $property,
        array $maintenanceData
    ): MaintenanceRecord {
        return $property->maintenanceRecords()->create([
            'issue_type' => $maintenanceData['issue_type'],
            'description' => $maintenanceData['description'],
            'priority' => $maintenanceData['priority'],
            'requested_by' => $maintenanceData['requested_by'],
            'status' => $maintenanceData['status'] ?? 'pending',
            'scheduled_date' => $maintenanceData['scheduled_date'] ?? null,
            'cost' => $maintenanceData['cost'] ?? null,
            'notes' => $maintenanceData['notes'] ?? null,
        ]);
    }

    /**
     * Update maintenance request status
     */
    public function updateMaintenanceStatus(
        MaintenanceRecord $record,
        string $status,
        ?array $additionalData = null
    ): void {
        $data = ['status' => $status];

        if ($status === 'completed') {
            $data['completed_date'] = now();
            if (isset($additionalData['cost'])) {
                $data['cost'] = $additionalData['cost'];
            }
        }

        $record->update($data);
    }

    /**
     * Get maintenance analytics for a property
     */
    public function getMaintenanceAnalytics(Property $property): array
    {
        $records = $property->maintenanceRecords;

        return [
            'request_status' => [
                'pending' => $records->where('status', 'pending')->count(),
                'in_progress' => $records->where('status', 'in_progress')->count(),
                'completed' => $records->where('status', 'completed')->count(),
            ],
            'priority_breakdown' => [
                'urgent' => $records->where('priority', 'urgent')->count(),
                'high' => $records->where('priority', 'high')->count(),
                'medium' => $records->where('priority', 'medium')->count(),
                'low' => $records->where('priority', 'low')->count(),
            ],
            'cost_analysis' => [
                'total_cost' => $records->sum('cost'),
                'average_cost' => $records->avg('cost'),
                'cost_by_priority' => $records->groupBy('priority')
                    ->map(fn ($group) => $group->sum('cost')),
            ],
        ];
    }

    private function calculateAverageCompletionTime(Collection $records): float
    {
        $completedRecords = $records->where('status', 'completed')
            ->where('completed_date', '!=', null);

        if ($completedRecords->isEmpty()) {
            return 0;
        }

        $totalDays = $completedRecords->sum(function ($record) {
            $created = Carbon::parse($record->created_at);
            $completed = Carbon::parse($record->completed_date);
            return $created->diffInDays($completed);
        });

        return $totalDays / $completedRecords->count();
    }
}
