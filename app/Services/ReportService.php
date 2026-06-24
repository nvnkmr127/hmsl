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

        $billTypeExpr = "CASE
            WHEN admission_id IS NOT NULL THEN 'IPD'
            WHEN consultation_id IS NOT NULL THEN 'OPD'
            ELSE 'Other'
        END";

        $revenueByType = Bill::query()
            ->whereBetween('created_at', [$filter->from . ' 00:00:00', $filter->to . ' 23:59:59'])
            ->selectRaw($billTypeExpr . ' as bill_type, SUM(total_amount) as total')
            ->groupByRaw($billTypeExpr)
            ->get()
            ->pluck('total', 'bill_type')
            ->toArray();

        $dailyPayments = (clone $query)
            ->where('type', 'payment')
            ->select(DB::raw('DATE(received_at) as date'), DB::raw('SUM(amount) as amount'))
            ->groupBy('date')
            ->get()
            ->pluck('amount', 'date')
            ->toArray();

        $dailyRefunds = (clone $query)
            ->where('type', 'refund')
            ->select(DB::raw('DATE(received_at) as date'), DB::raw('SUM(amount) as amount'))
            ->groupBy('date')
            ->get()
            ->pluck('amount', 'date')
            ->toArray();

        $dailyPatients = Bill::query()
            ->whereBetween('created_at', [$filter->from . ' 00:00:00', $filter->to . ' 23:59:59'])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(DISTINCT patient_id) as count'))
            ->groupBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        $dailyTrend = [];
        $dates = array_unique(array_merge(array_keys($dailyPayments), array_keys($dailyRefunds), array_keys($dailyPatients)));
        sort($dates);

        foreach ($dates as $date) {
            $payment = (float) ($dailyPayments[$date] ?? 0);
            $refund = (float) ($dailyRefunds[$date] ?? 0);
            $dailyTrend[] = [
                'date' => $date,
                'patients' => $dailyPatients[$date] ?? 0,
                'gross' => $payment,
                'refunds' => $refund,
                'net' => $payment - $refund,
            ];
        }

        return [
            'summary' => [
                'gross_collection' => (float) $totalCollected,
                'total_refunds' => (float) $totalRefunded,
                'net_collection' => (float) $netCollection,
            ],
            'method_breakdown' => $methodBreakdown,
            'revenue_by_type' => $revenueByType,
            'daily_trend' => $dailyTrend,
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

        if ($filter->doctorId) {
            $admissions->where('doctor_id', $filter->doctorId);
        }

        $dailyTrend = (clone $admissions)
            ->select(DB::raw('DATE(admission_date) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        $wardDistribution = (clone $admissions)
            ->join('beds', 'admissions.bed_id', '=', 'beds.id')
            ->join('wards', 'beds.ward_id', '=', 'wards.id')
            ->select('wards.name', DB::raw('count(*) as count'))
            ->groupBy('wards.name')
            ->get()
            ->pluck('count', 'name')
            ->toArray();

        return [
            'summary' => [
                'total_admissions' => (clone $admissions)->count(),
                'active_admissions' => (clone $admissions)->where('admissions.status', 'Admitted')->count(),
                'discharges' => Admission::whereBetween('discharge_date', [$filter->from, $filter->to])->count(),
            ],
            'daily_trend' => $dailyTrend,
            'ward_distribution' => $wardDistribution,
        ];
    }

    public function getRegistrationStats(ReportFilter $filter): array
    {
        $query = Patient::query()
            ->whereBetween('created_at', [$filter->from . ' 00:00:00', $filter->to . ' 23:59:59']);

        if ($filter->gender) {
            $query->where('gender', $filter->gender);
        }

        if ($filter->city) {
            $query->where('city', $filter->city);
        }

        if ($filter->ageGroup) {
            $now = Carbon::now();
            switch ($filter->ageGroup) {
                case '0-1 Year':
                    $query->whereBetween('date_of_birth', [$now->copy()->subYear(), $now]);
                    break;
                case '1-5 Years':
                    $query->whereBetween('date_of_birth', [$now->copy()->subYears(5), $now->copy()->subYear()]);
                    break;
                case '5-12 Years':
                    $query->whereBetween('date_of_birth', [$now->copy()->subYears(12), $now->copy()->subYears(5)]);
                    break;
                case '12+ Years':
                    $query->where('date_of_birth', '<=', $now->copy()->subYears(12));
                    break;
            }
        }

        $totalRegistrations = (clone $query)->count();

        $genderDistribution = (clone $query)
            ->select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get()
            ->pluck('count', 'gender')
            ->toArray();

        // Calculate age distribution exactly based on the query records
        $ageDistribution = [
            '0-1 Year' => 0,
            '1-5 Years' => 0,
            '5-12 Years' => 0,
            '12+ Years' => 0,
        ];

        $patientsDob = (clone $query)->select('date_of_birth')->whereNotNull('date_of_birth')->get();
        foreach ($patientsDob as $patient) {
            $age = Carbon::parse($patient->date_of_birth)->diffInYears(now());
            if ($age <= 1) {
                $ageDistribution['0-1 Year']++;
            } elseif ($age <= 5) {
                $ageDistribution['1-5 Years']++;
            } elseif ($age <= 12) {
                $ageDistribution['5-12 Years']++;
            } else {
                $ageDistribution['12+ Years']++;
            }
        }

        $dailyTrend = (clone $query)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        $villageDistribution = (clone $query)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->select('city', DB::raw('count(*) as count'))
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->pluck('count', 'city')
            ->toArray();

        return [
            'summary' => [
                'total_registrations' => $totalRegistrations,
            ],
            'gender_distribution' => $genderDistribution,
            'age_distribution' => $ageDistribution,
            'daily_trend' => $dailyTrend,
            'village_distribution' => $villageDistribution,
        ];
    }
}
