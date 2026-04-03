<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Bill;
use App\Models\Consultation;
use App\Models\Doctor;
use Illuminate\Support\Facades\Cache;

class StatsService
{
    /**
     * Get dashboard metrics for a user.
     * Uses caching to ensure performance under load.
     */
    public function getDashboardMetrics(?int $userId = null): array
    {
        $today = now()->toDateString();
        $doctor = $userId ? Doctor::where('user_id', $userId)->first() : null;

        // Cache metrics for 5 minutes to reduce DB load
        $cacheKey = 'dashboard_metrics_' . ($userId ?: 'admin') . '_' . $today;

        return Cache::remember($cacheKey, 300, function () use ($today, $doctor) {
            $revenueTrend = [];
            $tokensTrend = [];
            $dates = [];

            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                $dates[] = now()->subDays($i)->format('d M');
                
                $rev = Consultation::whereDate('consultation_date', $date)->where('payment_status', 'Paid')->sum('fee') 
                     + Bill::whereDate('created_at', $date)->where('payment_status', 'Paid')->whereNull('consultation_id')->sum('total_amount');
                
                $tok = Consultation::whereDate('consultation_date', $date)->count();
                
                $revenueTrend[] = (float) $rev;
                $tokensTrend[] = (int) $tok;
            }

            return [
                'opdToday' => Consultation::whereDate('consultation_date', $today)->count(),
                'opdPendingToday' => Consultation::whereDate('consultation_date', $today)->where('status', 'Pending')->count(),
                'ipdAdmitted' => Admission::where('status', 'Admitted')->count(),
                'billsToday' => Bill::whereDate('created_at', $today)->count(),
                'revenueToday' => Consultation::whereDate('consultation_date', $today)->where('payment_status', 'Paid')->sum('fee') 
                                 + Bill::whereDate('created_at', $today)->where('payment_status', 'Paid')->whereNull('consultation_id')->sum('total_amount'),

                'doctorPendingToday' => $doctor
                    ? Consultation::whereDate('consultation_date', $today)->where('doctor_id', $doctor->id)->where('status', 'Pending')->count()
                    : 0,
                
                'trend' => [
                    'dates' => $dates,
                    'revenue' => $revenueTrend,
                    'tokens' => $tokensTrend,
                ]
            ];
        });
    }

    /**
     * Clear the metrics cache.
     */
    public function clearCache(?int $userId = null): void
    {
        $today = now()->toDateString();
        Cache::forget('dashboard_metrics_' . ($userId ?: 'admin') . '_' . $today);
    }
}
