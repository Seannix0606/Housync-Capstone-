<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\TenantAssignment;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Landlord reports dashboard.
     */
    public function index(Request $request)
    {
        $landlordId = Auth::id();
        $year = $request->get('year', now()->year);
        $month = $request->get('month');

        // Financial summary
        $financial = $this->getFinancialSummary($landlordId, $year, $month);

        // Occupancy summary
        $occupancy = $this->getOccupancySummary($landlordId);

        // Maintenance summary
        $maintenance = $this->getMaintenanceSummary($landlordId, $year, $month);

        // Monthly revenue trend (last 12 months)
        $revenueTrend = $this->getRevenueTrend($landlordId);

        // Per-property performance
        $propertyPerformance = $this->getPropertyPerformance($landlordId);

        // Lease expirations in next 60 days
        $upcomingExpirations = TenantAssignment::where('landlord_id', $landlordId)
            ->where('status', 'active')
            ->whereNotNull('lease_end_date')
            ->where('lease_end_date', '<=', now()->addDays(60))
            ->where('lease_end_date', '>=', now())
            ->with(['tenant', 'unit.property'])
            ->orderBy('lease_end_date')
            ->get();

        $availableYears = range(now()->year - 2, now()->year);

        return view('landlord.reports.index', compact(
            'financial',
            'occupancy',
            'maintenance',
            'revenueTrend',
            'propertyPerformance',
            'upcomingExpirations',
            'year',
            'month',
            'availableYears'
        ));
    }

    protected function getFinancialSummary(int $landlordId, int $year, ?int $month = null): array
    {
        $baseQuery = Bill::where('landlord_id', $landlordId)->whereYear('created_at', $year);

        if ($month) {
            $baseQuery->whereMonth('created_at', $month);
        }

        $totalBilled = (clone $baseQuery)->sum('amount');
        $totalCollected = (clone $baseQuery)->sum('amount_paid');
        $totalOutstanding = (clone $baseQuery)->sum('balance');
        $collectionRate = $totalBilled > 0 ? round(($totalCollected / $totalBilled) * 100, 1) : 0;

        $overdueCount = (clone $baseQuery)->where('status', 'overdue')->count();
        $unpaidCount = (clone $baseQuery)->where('status', 'unpaid')->count();

        return [
            'total_billed' => $totalBilled,
            'total_collected' => $totalCollected,
            'total_outstanding' => $totalOutstanding,
            'collection_rate' => $collectionRate,
            'overdue_count' => $overdueCount,
            'unpaid_count' => $unpaidCount,
        ];
    }

    protected function getOccupancySummary(int $landlordId): array
    {
        $properties = Property::where('landlord_id', $landlordId)->with('units')->get();

        $totalUnits = 0;
        $occupiedUnits = 0;
        $availableUnits = 0;
        $maintenanceUnits = 0;

        foreach ($properties as $property) {
            foreach ($property->units as $unit) {
                $totalUnits++;
                match ($unit->status) {
                    'occupied' => $occupiedUnits++,
                    'available' => $availableUnits++,
                    'maintenance' => $maintenanceUnits++,
                    default => null,
                };
            }
        }

        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0;
        $vacancyRate = $totalUnits > 0 ? round(($availableUnits / $totalUnits) * 100, 1) : 0;

        return [
            'total_units' => $totalUnits,
            'occupied' => $occupiedUnits,
            'available' => $availableUnits,
            'maintenance' => $maintenanceUnits,
            'occupancy_rate' => $occupancyRate,
            'vacancy_rate' => $vacancyRate,
            'properties_count' => $properties->count(),
        ];
    }

    protected function getMaintenanceSummary(int $landlordId, int $year, ?int $month = null): array
    {
        $baseQuery = MaintenanceRequest::where('landlord_id', $landlordId)->whereYear('created_at', $year);

        if ($month) {
            $baseQuery->whereMonth('created_at', $month);
        }

        $total = (clone $baseQuery)->count();
        $pending = (clone $baseQuery)->where('status', 'pending')->count();
        $inProgress = (clone $baseQuery)->whereIn('status', ['assigned', 'in_progress'])->count();
        $completed = (clone $baseQuery)->where('status', 'completed')->count();
        $cancelled = (clone $baseQuery)->where('status', 'cancelled')->count();

        $avgResolutionDays = (clone $baseQuery)
            ->where('status', 'completed')
            ->whereNotNull('completed_date')
            ->selectRaw('AVG(DATEDIFF(completed_date, requested_date)) as avg_days')
            ->value('avg_days');

        $byCategory = (clone $baseQuery)
            ->select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        $byPriority = (clone $baseQuery)
            ->select('priority', DB::raw('COUNT(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        $avgRating = (clone $baseQuery)
            ->where('status', 'completed')
            ->whereNotNull('rating')
            ->avg('rating');

        return [
            'total' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'avg_resolution_days' => $avgResolutionDays ? round($avgResolutionDays, 1) : null,
            'by_category' => $byCategory,
            'by_priority' => $byPriority,
            'avg_rating' => $avgRating ? round($avgRating, 1) : null,
        ];
    }

    protected function getRevenueTrend(int $landlordId): array
    {
        $trend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabel = $date->format('M Y');

            $billed = Bill::where('landlord_id', $landlordId)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');

            $collected = Bill::where('landlord_id', $landlordId)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount_paid');

            $trend[] = [
                'month' => $monthLabel,
                'billed' => round($billed, 2),
                'collected' => round($collected, 2),
            ];
        }

        return $trend;
    }

    protected function getPropertyPerformance(int $landlordId): array
    {
        $properties = Property::where('landlord_id', $landlordId)
            ->withCount(['units', 'maintenanceRequests'])
            ->get();

        $performance = [];

        foreach ($properties as $property) {
            $occupiedUnits = $property->units()->where('status', 'occupied')->count();
            $totalUnits = $property->units_count;
            $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0;

            $monthlyRevenue = Bill::where('landlord_id', $landlordId)
                ->whereHas('unit', function ($query) use ($property) {
                    $query->where('property_id', $property->id);
                })
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount_paid');

            $performance[] = [
                'id' => $property->id,
                'name' => $property->name,
                'total_units' => $totalUnits,
                'occupied_units' => $occupiedUnits,
                'occupancy_rate' => $occupancyRate,
                'monthly_revenue' => round($monthlyRevenue, 2),
                'maintenance_count' => $property->maintenance_requests_count,
            ];
        }

        return $performance;
    }

    /**
     * Export financial report as CSV.
     */
    public function exportFinancial(Request $request)
    {
        $landlordId = Auth::id();
        $year = $request->get('year', now()->year);

        $bills = Bill::where('landlord_id', $landlordId)
            ->whereYear('created_at', $year)
            ->with(['tenant', 'unit.property'])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = "financial-report-{$year}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($bills) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Invoice #', 'Tenant', 'Unit', 'Property', 'Type', 'Amount', 'Paid', 'Balance', 'Status', 'Due Date', 'Created']);

            foreach ($bills as $bill) {
                fputcsv($file, [
                    $bill->invoice_number,
                    $bill->tenant?->name ?? 'N/A',
                    $bill->unit?->unit_number ?? 'N/A',
                    $bill->unit?->property?->name ?? 'N/A',
                    ucfirst($bill->type),
                    number_format($bill->amount, 2),
                    number_format($bill->amount_paid, 2),
                    number_format($bill->balance, 2),
                    ucfirst($bill->status),
                    $bill->due_date?->format('Y-m-d') ?? 'N/A',
                    $bill->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
