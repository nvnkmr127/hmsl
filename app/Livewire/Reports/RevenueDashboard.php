<?php

namespace App\Livewire\Reports;

use App\Models\Bill;
use App\Models\BillItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RevenueDashboard extends Component
{
    public $dateRange = 'today';
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->startDate = now()->startOfDay()->format('Y-m-d');
        $this->endDate = now()->endOfDay()->format('Y-m-d');
    }

    public function updatedDateRange($value)
    {
        switch ($value) {
            case 'today':
                $this->startDate = now()->startOfDay()->format('Y-m-d');
                $this->endDate = now()->endOfDay()->format('Y-m-d');
                break;
            case 'yesterday':
                $this->startDate = now()->subDay()->startOfDay()->format('Y-m-d');
                $this->endDate = now()->subDay()->endOfDay()->format('Y-m-d');
                break;
            case 'this_week':
                $this->startDate = now()->startOfWeek()->format('Y-m-d');
                $this->endDate = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'this_month':
                $this->startDate = now()->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->endOfMonth()->format('Y-m-d');
                break;
        }
    }

    public function render()
    {
        $baseQuery = \App\Models\BillPayment::query()
            ->whereBetween('received_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);

        // TOTAL REVENUE = Payments - Refunds
        $totalReceived = (clone $baseQuery)->where('type', 'payment')->sum('amount');
        $totalRefunded = (clone $baseQuery)->where('type', 'refund')->sum('amount');
        $totalRevenue = $totalReceived - $totalRefunded;

        $totalBills = Bill::whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])->count();
        $totalDiscount = Bill::whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])->sum('discount_amount');
        $totalTax = Bill::whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])->sum('tax_amount');

        $paymentMethodSplit = (clone $baseQuery)
            ->select('method as payment_method', DB::raw('SUM(CASE WHEN type = "payment" THEN amount ELSE -amount END) as total'))
            ->groupBy('method')
            ->get();

        $departmentSplit = BillItem::query()
            ->whereHas('bill', function($q) {
                $q->where(fn($bq) => $bq->where('payment_status', '=', 'Paid'))
                  ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
            })
            ->select('item_type', DB::raw('SUM(total_price) as total'))
            ->groupBy('item_type')
            ->get();

        // Daily trend for the current range (max 30 days)
        $dailyTrend = (clone $query)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $recentBills = (clone $query)
            ->with(['patient'])
            ->latest()
            ->limit(10)
            ->get();

        $doctorDiscountSplit = \App\Models\BillDiscount::query()
            ->where('status', 'approved')
            ->whereBetween('applied_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->with('doctor')
            ->select('doctor_id', DB::raw('SUM(applied_amount) as total'))
            ->groupBy('doctor_id')
            ->get();

        return view('livewire.reports.revenue-dashboard', [
            'totalRevenue' => $totalRevenue,
            'totalBills' => $totalBills,
            'totalDiscount' => $totalDiscount,
            'totalTax' => $totalTax,
            'paymentMethodSplit' => $paymentMethodSplit,
            'departmentSplit' => $departmentSplit,
            'doctorDiscountSplit' => $doctorDiscountSplit,
            'dailyTrend' => $dailyTrend,
            'recentBills' => $recentBills,
        ]);
    }
}
