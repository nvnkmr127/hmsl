<?php

namespace App\Services\Webhooks\Factories;

use Illuminate\Support\Facades\DB;

class SystemPayloadFactory
{
    public static function createDailySummary(?string $date = null): array
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
        
        $visitSplit = \App\Models\Consultation::whereDate('consultation_date', $date)
            ->select('visit_type', DB::raw('COUNT(*) as count'))
            ->groupBy('visit_type')
            ->get()
            ->pluck('count', 'visit_type')
            ->toArray();

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

        return [
            'date' => $date,
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
