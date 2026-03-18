<?php

namespace App\Livewire\Counter;

use App\Models\Patient;
use App\Models\Consultation;
use App\Models\PatientVital;
use App\Models\Bill;
use Livewire\Component;

class PatientHistory extends Component
{
    public $patientId;
    public $patient;

    public function mount($id)
    {
        $this->patientId = $id;
        $this->patient = Patient::findOrFail($id);
    }

    public function render()
    {
        $id = $this->patientId;

        // 1. Visits & History
        $visits = Consultation::with(['doctor.user'])
            ->where(fn($q) => $q->where('patient_id', '=', $id))
            ->latest()
            ->get();

        $vitals = PatientVital::where(fn($q) => $q->where('patient_id', '=', $id))
            ->latest()
            ->get();

        // 2. Timeline Logic (OP + Admissions)
        $timeline = collect();

        foreach ($visits as $v) {
            $timeline->push((object)[
                'date' => $v->consultation_date,
                'type' => 'OP Visit',
                'title' => "Token #{$v->token_number}",
                'meta' => $v->notes ?: 'Regular checkup',
                'color' => 'blue'
            ]);
        }

        $admissions = \App\Models\Admission::where('patient_id', $id)->get();
        foreach ($admissions as $a) {
            $timeline->push((object)[
                'date' => $a->admission_date,
                'type' => 'Admission',
                'title' => 'In-Patient Admission',
                'meta' => "Ward: {$a->ward_name}",
                'color' => 'red'
            ]);
            if ($a->discharge_date) {
                $timeline->push((object)[
                    'date' => $a->discharge_date,
                    'type' => 'Discharge',
                    'title' => 'Process Completed',
                    'meta' => 'Patient Discharged',
                    'color' => 'emerald'
                ]);
            }
        }

        $timeline = $timeline->sortByDesc('date');

        // 3. All Bills for PDF access
        $allBills = Bill::where('patient_id', $id)->orderBy('created_at', 'desc')->get();
        foreach ($allBills as $b) {
            $timeline->push((object)[
                'date' => $b->created_at,
                'type' => 'Billing',
                'title' => "Invoice #{$b->bill_number}",
                'meta' => "Amount: " . number_format($b->total_amount, 2) . " ({$b->payment_status})",
                'color' => 'amber',
                'bill_id' => $b->id
            ]);
        }
        $timeline = $timeline->sortByDesc('date');

        // 4. Billing Summary
        $thirtyDaysAgo = now()->subDays(30);
        $totalThirtyDays = Bill::where('patient_id', $id)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->sum('total_amount');

        $lastBill = Bill::where('patient_id', $id)->latest()->first();
        $todayBill = Bill::where('patient_id', $id)->whereDate('created_at', date('Y-m-d'))->first();

        // 5. Alerts Logic
        $alerts = [];
        if ($this->patient->allergies) {
            $alerts[] = ['type' => 'danger', 'label' => 'Allergy', 'msg' => $this->patient->allergies];
        }
        
        $visitCount = $visits->where('consultation_date', '>=', now()->subMonths(3))->count();
        if ($visitCount > 5) {
            $alerts[] = ['type' => 'warning', 'label' => 'Frequent', 'msg' => "{$visitCount} visits in 3 months"];
        }

        $latestTemp = $vitals->first()?->temperature;
        if ($latestTemp && $latestTemp > 100) {
            $alerts[] = ['type' => 'danger', 'label' => 'High Temp', 'msg' => "{$latestTemp}°F recorded recently"];
        }

        // Vaccination data
        $vaccinations = \App\Models\PatientVaccination::with('vaccine')
            ->where(fn($q) => $q->where('patient_id', '=', $id))
            ->get();
        $allVaccines = \App\Models\Vaccine::orderBy('sequence_order')->get();

        return view('livewire.counter.patient-history', [
            'visits' => $visits,
            'vitals' => $vitals,
            'timeline' => $timeline,
            'billStats' => [
                'thirty_days' => $totalThirtyDays,
                'last_bill' => $lastBill,
                'today_bill' => $todayBill
            ],
            'allBills' => $allBills,
            'alerts' => $alerts,
            'vaccinations' => $vaccinations,
            'allVaccines' => $allVaccines,
        ]);
    }

    public function recordVaccination($vaccineId, $date)
    {
        \App\Models\PatientVaccination::updateOrCreate(
            ['patient_id' => $this->patientId, 'vaccine_id' => $vaccineId],
            ['date_given' => $date ?: date('Y-m-d')]
        );
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Vaccination status updated!']);
    }
}
