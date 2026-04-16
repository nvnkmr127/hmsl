<?php

namespace App\Livewire\Reports;

use App\Models\Doctor;
use App\Models\Consultation;
use App\Models\Bill;
use Livewire\Component;
use Livewire\WithPagination;

class DoctorPerformanceReport extends Component
{
    use WithPagination;

    public $dateFrom;
    public $dateTo;

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function render()
    {
        $doctors = Doctor::withCount(['consultations' => function($q) {
                $q->whereBetween('consultation_date', [$this->dateFrom, $this->dateTo]);
            }])
            ->get()
            ->map(function($doctor) {
                $earnings = Bill::whereHas('consultation', function($q) use ($doctor) {
                        $q->where('doctor_id', $doctor->id)
                          ->whereBetween('consultation_date', [$this->dateFrom, $this->dateTo]);
                    })
                    ->where('payment_status', 'Paid')
                    ->with(['items' => function($q) {
                        $q->where('item_type', 'Consultation');
                    }])
                    ->get()
                    ->sum(function($bill) {
                        return $bill->items->where('item_type', 'Consultation')->sum('total_price');
                    });
                
                $doctor->total_earnings = $earnings;
                return $doctor;
            })
            ->sortByDesc('consultations_count');

        return view('livewire.reports.doctor-performance-report', [
            'doctors' => $doctors
        ]);
    }
}
