<?php

namespace App\Services;

use App\Models\Property;
use App\Models\ManagementContract;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PropertyManagementService
{
    public function __construct(
        private readonly MaintenanceService $maintenanceService,
        private readonly FinancialService $financialService
    ) {}

    /**
     * Get all properties with active management contracts
     */
    public function getManagedProperties(): Collection
    {
        return Property::query()
            ->whereHas('managementContracts', function ($query) {
                $query->where('status', 'active');
            })
            ->with([
                'managementContracts' => fn($query) => $query->where('status', 'active'),
                'tenantInfo',
                'maintenanceRecords',
                'financialRecords'
            ])
            ->get();
    }

    /**
     * Create a new management contract
     */
    public function createContract(Property $property, array $contractData): ManagementContract
    {
        return DB::transaction(function () use ($property, $contractData) {
            $contract = $property->managementContracts()->create([
                'admin_id' => auth()->id(),
                'contract_type' => $contractData['contract_type'],
                'management_fee_percentage' => $contractData['management_fee_percentage'],
                'base_fee' => $contractData['base_fee'] ?? null,
                'start_date' => $contractData['start_date'],
                'end_date' => $contractData['end_date'],
                'payment_schedule' => $contractData['payment_schedule'],
                'services_included' => $contractData['services_included'],
                'special_terms' => $contractData['special_terms'] ?? null,
                'status' => 'active',
            ]);

            // Record initial management fee as a financial record
            $this->financialService->recordManagementFee($property, $contract);

            return $contract;
        });
    }

    /**
     * Get contract analytics for a property
     */
    public function getContractAnalytics(Property $property): array
    {
        $activeContract = $property->managementContracts()->active()->first();
        $allContracts = $property->managementContracts;

        return [
            'has_active_contract' => $activeContract !== null,
            'total_contracts' => $allContracts->count(),
            'current_fee_structure' => $activeContract ? [
                'base_fee' => $activeContract->base_fee,
                'percentage' => $activeContract->management_fee_percentage,
            ] : null,
            'contract_history' => $allContracts->map(fn ($contract) => [
                'id' => $contract->id,
                'type' => $contract->contract_type,
                'start_date' => $contract->start_date->format('Y-m-d'),
                'end_date' => $contract->end_date->format('Y-m-d'),
                'status' => $contract->status,
            ]),
        ];
    }

    /**
     * Update contract status
     */
    public function updateContractStatus(ManagementContract $contract, string $status): void
    {
        $contract->update(['status' => $status]);

        if ($status === 'terminated') {
            $contract->update(['end_date' => now()]);
        }
    }

    /**
     * Get managed properties summary
     */
    public function getManagedPropertiesSummary(): array
    {
        $properties = Property::whereHas('managementContracts', function ($query) {
            $query->active();
        })->with(['managementContracts' => function ($query) {
            $query->active();
        }])->get();

        return [
            'total_properties' => $properties->count(),
            'total_revenue' => $this->financialService->calculateTotalRevenue($properties),
            'total_expenses' => $this->financialService->calculateTotalExpenses($properties),
            'maintenance_stats' => $this->maintenanceService->getMaintenanceStatistics($properties),
        ];
    }
}
