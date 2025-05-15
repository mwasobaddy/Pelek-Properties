<?php

namespace App\Services;

use App\Models\Property;
use App\Models\FinancialRecord;
use App\Models\ManagementContract;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinancialService
{
    /**
     * Record a financial transaction
     */
    public function recordTransaction(
        Property $property,
        array $transactionData
    ): FinancialRecord {
        return $property->financialRecords()->create([
            'transaction_type' => $transactionData['type'],
            'category' => $transactionData['category'],
            'amount' => $transactionData['amount'],
            'transaction_date' => $transactionData['date'] ?? now(),
            'payment_method' => $transactionData['payment_method'] ?? null,
            'status' => $transactionData['status'] ?? 'pending',
            'description' => $transactionData['description'] ?? null,
            'reference_number' => $transactionData['reference_number'] ?? null,
            'recorded_by' => auth()->id(),
        ]);
    }

    /**
     * Record management fee
     */
    public function recordManagementFee(
        Property $property,
        ManagementContract $contract
    ): void {
        $baseAmount = $contract->base_fee ?? 0;
        $percentageAmount = 0;

        if ($contract->management_fee_percentage > 0) {
            $monthlyRent = $this->getMonthlyRentAmount($property);
            $percentageAmount = ($monthlyRent * $contract->management_fee_percentage) / 100;
        }

        $this->recordTransaction($property, [
            'type' => 'expense',
            'category' => 'management_fee',
            'amount' => $baseAmount + $percentageAmount,
            'description' => "Management fee for " . now()->format('F Y'),
            'status' => 'pending'
        ]);
    }

    /**
     * Calculate total revenue for properties
     */
    public function calculateTotalRevenue(Collection $properties): float
    {
        return $properties->sum(function ($property) {
            return $property->financialRecords()
                ->income()
                ->completed()
                ->sum('amount');
        });
    }

    /**
     * Calculate total expenses for properties
     */
    public function calculateTotalExpenses(Collection $properties): float
    {
        return $properties->sum(function ($property) {
            return $property->financialRecords()
                ->expense()
                ->completed()
                ->sum('amount');
    });
    }

    /**
     * Get financial analytics for a property
     */
    public function getFinancialAnalytics(Property $property): array
    {
        $currentMonth = now()->startOfMonth();
        $records = $property->financialRecords()
            ->where('transaction_date', '>=', $currentMonth)
            ->get();

        return [
            'current_month' => [
                'revenue' => $records->where('transaction_type', 'income')->sum('amount'),
                'expenses' => $records->where('transaction_type', 'expense')->sum('amount'),
                'pending_payments' => $records->where('status', 'pending')->count(),
            ],
            'categories' => $this->getCategoryBreakdown($property),
            'payment_status' => $this->getPaymentStatusBreakdown($property),
        ];
    }

    private function getMonthlyRentAmount(Property $property): float
    {
        if ($property->listing_type === 'rent') {
            return $property->rental_price_monthly ?? 0;
        } elseif ($property->listing_type === 'commercial') {
            return $property->commercial_lease_rate ?? 0;
        }
        return 0;
    }

    private function getCategoryBreakdown(Property $property): array
    {
        return $property->financialRecords()
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category')
            ->toArray();
    }

    private function getPaymentStatusBreakdown(Property $property): array
    {
        return $property->financialRecords()
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }
}
