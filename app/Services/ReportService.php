<?php

namespace App\Services;

use App\DTOs\ReportFilter;
use App\Models\Admission;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Consultation;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get OPD visit statistics.
     */
    public function getOpdStats(ReportFilter $filter): array
    {
        $query = Consultation::query()
            ->whereBetween('consultation_date', [$filter->from, $filter->to]);

        if ($filter->doctorId) {
            $query->where('doctor_id', $filter->doctorId);
        }

        if ($filter->departmentId) {
            $query->whereHas('doctor', fn($q) => $q->where('department_id', $filter->departmentId));
        }

        $totalVisits = (clone $query)->count();
        
        $visitTypes = (clone $query)
            ->select('visit_type', DB::raw('count(*) as count'))
            ->groupBy('visit_type')
            ->get()
            ->pluck('count', 'visit_type')
            ->toArray();

        $doctorWise = (clone $query)
            ->with('doctor:id,full_name')
            ->select('doctor_id', DB::raw('count(*) as count'))
            ->groupBy('doctor_id')
            ->get()
            ->mapWithKeys(fn($item) => [$item->doctor->full_name ?? "Doc #{$item->doctor_id}" => $item->count])
            ->toArray();

        $dailyTrend = (clone $query)
            ->select(DB::raw('DATE(consultation_date) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        return [
            'summary' => [
                'total_visits' => $totalVisits,
                'visit_types' => $visitTypes,
            ],
            'doctor_wise' => $doctorWise,
            'daily_trend' => $dailyTrend,
        ];
    }

    /**
     * Get Financial / Revenue statistics.
     */
    public function getRevenueStats(ReportFilter $filter): array
    {
        $query = BillPayment::query()
            ->whereBetween('received_at', [$filter->from . ' 00:00:00', $filter->to . ' 23:59:59']);

        if ($filter->paymentMethod) {
            $query->where('method', $filter->paymentMethod);
        }

        $totalCollected = (clone $query)->where('type', 'payment')->sum('amount');
        $totalRefunded = (clone $query)->where('type', 'refund')->sum('amount');
        $netCollection = $totalCollected - $totalRefunded;

        $methodBreakdown = (clone $query)
            ->where('type', 'payment')
            ->select('method', DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->get()
            ->pluck('total', 'method')
            ->toArray();

        $revenueByType = Bill::query()
            ->whereBetween('created_at', [$filter->from . ' 00:00:00', $filter->to . ' 23:59:59'])
            ->select('bill_type', DB::raw('SUM(total_amount) as total'))
            ->groupBy('bill_type')
            ->get()
            ->pluck('total', 'bill_type')
            ->toArray();

        return [
            'summary' => [
                'gross_collection' => (float) $totalCollected,
                'total_refunds' => (float) $totalRefunded,
                'net_collection' => (float) $netCollection,
            ],
            'method_breakdown' => $methodBreakdown,
            'revenue_by_type' => $revenueByType,
        ];
    }

    /**
     * Get IPD / Admission statistics.
     */
    public function getIpdStats(ReportFilter $filter): array
    {
        $admissions = Admission::query()
            ->whereBetween('admission_date', [$filter->from, $filter->to]);

        if ($filter->wardId) {
            $admissions->whereHas('bed', fn($q) => $q->where('ward_id', $filter->wardId));
        }

        return [
            'total_admissions' => $admissions->count(),
            'active_admissions' => Admission::where('status', 'Admitted')->count(),
            'discharges' => Admission::whereBetween('discharge_date', [$filter->from, $filter->to])->count(),
        ];
    }
}
