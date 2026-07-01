<?php

namespace App\Services\Webhooks\Factories;

use Illuminate\Support\Facades\DB;

class SystemPayloadFactory
{
    public static function createDailySummary(?string $date = null, ?string $shift = null): array
    {
        $date = $date ?: now()->toDateString();
        
        // Revenue Metrics
        $totalPaid = (float) \App\Models\BillPayment::whereDate('received_at', $date)
            ->where('type', 'payment')
            ->sum('amount');
            
        $totalRefunded = (float) \App\Models\BillPayment::whereDate('received_at', $date)
            ->where('type', 'refund')
            ->sum('amount');
            
        $methodSplit = \App\Models\BillPayment::whereDate('received_at', $date)
            ->select('method', DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->get()
            ->pluck('total', 'method')
            ->toArray();

        // Consultation Metrics
        $consultsQuery = \App\Models\Consultation::whereDate('consultation_date', $date);
        $totalConsults = (int) $consultsQuery->count();
        
        $visitSplitRaw = \App\Models\Consultation::whereDate('consultation_date', $date)
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
        $doctorSplit = \App\Models\Consultation::whereDate('consultation_date', $date)
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
        $todayAdmissions = (int) \App\Models\Admission::whereDate('admission_date', $date)->count();

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
