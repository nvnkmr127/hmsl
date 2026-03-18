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
        $query = Bill::query()
            ->where(fn($q) => $q->where('payment_status', '=', 'Paid'))
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);

        $totalRevenue = (clone $query)->sum('total_amount');
        $totalBills = (clone $query)->count();
        $totalDiscount = (clone $query)->sum('discount_amount');
        $totalTax = (clone $query)->sum('tax_amount');

        $paymentMethodSplit = (clone $query)
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
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

        return view('livewire.reports.revenue-dashboard', [
            'totalRevenue' => $totalRevenue,
            'totalBills' => $totalBills,
            'totalDiscount' => $totalDiscount,
            'totalTax' => $totalTax,
            'paymentMethodSplit' => $paymentMethodSplit,
            'departmentSplit' => $departmentSplit,
            'dailyTrend' => $dailyTrend,
            'recentBills' => $recentBills,
        ]);
    }
}
