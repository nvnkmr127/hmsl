<?php

namespace App\Services\Webhooks\Factories;

use Illuminate\Support\Facades\DB;

class SystemPayloadFactory
{
    public static function createDailySummary(?string $date = null, ?string $shift = null): array
    {
        $date = $date ?: now()->toDateString();
        
        $start = null;
        $end = null;
        
        if ($shift === 'Day') {
            $start = $date . ' 10:00:00';
            $end = $date . ' 21:00:00';
        } elseif ($shift === 'Night') {
            $start = $date . ' 21:00:00';
            $end = \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d') . ' 10:00:00';
        }

        $applyTimeFilter = function ($query, $column) use ($date, $start, $end) {
            if ($start && $end) {
                return $query->whereBetween($column, [$start, $end]);
            }
            return $query->whereDate($column, $date);
        };
        
        // Revenue Metrics
        $totalPaid = (float) $applyTimeFilter(\App\Models\BillPayment::query(), 'received_at')
            ->where('type', 'payment')
            ->sum('amount');
            
        $totalRefunded = (float) $applyTimeFilter(\App\Models\BillPayment::query(), 'received_at')
            ->where('type', 'refund')
            ->sum('amount');
            
        $methodSplit = $applyTimeFilter(\App\Models\BillPayment::query(), 'received_at')
            ->select('method', DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->get()
            ->pluck('total', 'method')
            ->toArray();

        // Consultation Metrics
        // Note: Using created_at instead of consultation_date so we can filter by exact time
        $consultsQuery = $applyTimeFilter(\App\Models\Consultation::query(), 'created_at');
        $totalConsults = (int) $consultsQuery->count();
        
        $visitSplitRaw = $applyTimeFilter(\App\Models\Consultation::query(), 'created_at')
            ->where('status', '!=', 'Cancelled')
            ->select('visit_type', DB::raw('COUNT(*) as count'))
            ->groupBy('visit_type')
            ->pluck('count', 'visit_type')
            ->toArray();

        $visitSplit = [];
        foreach ($visitSplitRaw as $type => $count) {
            $key = $type === 'Follow-up' ? 'Review' : $type;
            $visitSplit[$key] = ($visitSplit[$key] ?? 0) + $count;
        }

        // Clinical Performance
        $doctorSplit = $applyTimeFilter(\App\Models\Consultation::query(), 'created_at')
            ->with('doctor')
            ->select('doctor_id', DB::raw('COUNT(*) as count'))
            ->groupBy('doctor_id')
            ->get()
            ->mapWithKeys(function($item) {
                $name = $item->doctor->full_name ?? "Doctor #{$item->doctor_id}";
                return [$name => (int)$item->count];
            })
            ->toArray();

        // IPD Metrics
        $activeAdmissions = (int) \App\Models\Admission::where('status', 'Admitted')->count();
        $todayAdmissions = (int) $applyTimeFilter(\App\Models\Admission::query(), 'admission_date')->count();

        $dateLabel = $shift ? "{$date} ({$shift} summary)" : $date;

        return [
            'date' => $dateLabel,
            'summary' => [
                'revenue' => [
                    'net_collection' => (float)($totalPaid - $totalRefunded),
                    'total_payments' => (float)$totalPaid,
                    'total_refunds' => (float)$totalRefunded,
                    'methods' => $methodSplit
                ],
                'opd' => [
                    'total_visits' => (int)$totalConsults,
                    'visit_types' => $visitSplit,
                    'doctor_performance' => $doctorSplit
                ],
                'ipd' => [
                    'currently_admitted' => (int)$activeAdmissions,
                    'new_admissions_today' => (int)$todayAdmissions
                ],
                'generated_at' => now()->toIso8601String()
            ]
        ];
    }
}
