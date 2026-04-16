<?php

namespace App\Livewire\Doctor;

use App\Models\Consultation;
use App\Models\Doctor;
use App\Services\OpdService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ConsultationDesk extends Component
{
    public $selectedConsultation;
    public $lastVisit;
    public $timeline = [];
    public $billStats = [];
    public $alerts = [];
    public $loadingSupport = false;
    public $discount = 0;

    public function getDoctorProperty(): ?Doctor
    {
        return Doctor::where('user_id', '=', Auth::id())->first();
    }

    public function assignMyselfAsDoctor()
    {
        if (!\App\Models\HospitalOwner::isOwner(Auth::user())) {
            return;
        }

        $user = Auth::user();
        $dept = \App\Models\Department::firstOrCreate(['name' => 'General'], ['is_active' => true]);

        $doctor = Doctor::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $user->name,
                'department_id' => $dept->id,
                'specialization' => 'General/On-Duty',
                'consultation_fee' => \App\Models\Setting::get('consultation_fee_default', 500),
                'is_active' => true
            ]
        );

        \App\Models\HospitalOwner::setOwnerDoctor($doctor);
        
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Profile linked successfully.']);
        return redirect()->route('doctor.dashboard');
    }

    public function selectConsultation($id)
    {
        $consultation = Consultation::with(['patient'])->findOrFail($id);
        
        // Update status only if it's currently Pending
        if ($consultation->status === 'Pending') {
            $consultation->update(['status' => 'Ongoing']); // 'Ongoing' will be displayed as 'With Doctor'
        }
        
        $this->selectedConsultation = $consultation;
        $this->discount = $consultation->discount_amount;
        $this->fetchCoreClinicalData($consultation);
        
        // Signal that core data is ready, now load support data
        $this->loadingSupport = true;
        $this->fetchSupportData($consultation);
    }

    protected function fetchCoreClinicalData($consultation)
    {
        $patient = $consultation->patient;
        $id = $patient->id;

        // 1. Last Visit
        $this->lastVisit = Consultation::where('patient_id', $id)
            ->where('id', '<', $consultation->id)
            ->where('status', 'Completed')
            ->latest()
            ->first();

        // 2. Timeline Logic
        $visits = Consultation::where('patient_id', $id)->latest()->get();
        $timeline = collect();

        foreach ($visits as $v) {
            $timeline->push((object)[
                'date' => \Carbon\Carbon::parse($v->consultation_date),
                'type' => 'OP Visit',
                'title' => "Token #{$v->token_number}",
                'meta' => $v->notes ?: 'Regular checkup',
                'color' => 'blue'
            ]);
        }

        $admissions = \App\Models\Admission::with('bed.ward')->where('patient_id', $id)->get();
        foreach ($admissions as $a) {
            $timeline->push((object)[
                'date' => \Carbon\Carbon::parse($a->admission_date),
                'type' => 'Admission',
                'title' => 'In-Patient Admission',
                'meta' => "Ward: " . (optional(optional($a->bed)->ward)->name ?? 'N/A'),
                'color' => 'red'
            ]);
        }
        $this->timeline = $timeline->sortByDesc('date')->take(5)->toArray();

        // 3. Early Alerts (Core clinical safety)
        $this->alerts = [];
        if ($patient->allergies) {
            $this->alerts[] = ['type' => 'danger', 'label' => 'Allergy', 'msg' => $patient->allergies];
        }
        
        $vitals = \App\Models\PatientVital::where('patient_id', $id)->latest()->first();
        if ($vitals && $vitals->temperature > 100) {
            $this->alerts[] = ['type' => 'danger', 'label' => 'High Temp', 'msg' => "{$vitals->temperature}°F recorded"];
        }
    }

    protected function fetchSupportData($consultation)
    {
        $id = $consultation->patient_id;

        // 1. Billing Summary (Load Later)
        $thirtyDaysAgo = now()->subDays(30);
        $this->billStats = [
            'thirty_days' => \App\Models\Bill::where('patient_id', $id)->where('created_at', '>=', $thirtyDaysAgo)->sum('total_amount'),
            'last_bill' => \App\Models\Bill::where('patient_id', $id)->latest()->first(),
            'today_bill' => \App\Models\Bill::where('patient_id', $id)->whereDate('created_at', date('Y-m-d'))->first()
        ];

        // 2. Secondary Alerts
        $visits = Consultation::where('patient_id', $id)->latest()->get();
        $visitCount = $visits->where('consultation_date', '>=', now()->subMonths(3))->count();
        if ($visitCount > 5) {
            $this->alerts[] = ['type' => 'warning', 'label' => 'Frequent', 'msg' => "{$visitCount} visits in 3 months"];
        }
        
        $this->loadingSupport = false;
    }

    public function updatedDiscount($value)
    {
        if ($this->selectedConsultation) {
            $this->selectedConsultation->update(['discount_amount' => $value]);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Discount updated.']);
        }
    }

    public function completeAndNext()
    {
        if ($this->selectedConsultation) {
            // Validate clinical notes before completion
            if (empty($this->selectedConsultation->chief_complaints) && empty($this->selectedConsultation->diagnosis_notes)) {
                $this->dispatch('notify', [
                    'type' => 'error', 
                    'message' => 'Cannot complete consultation without chief complaints or diagnosis notes.'
                ]);
                return;
            }

            $this->selectedConsultation->update(['status' => 'Completed']);
        }

        $doctor = $this->doctor;
        if (!$doctor) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'No active doctor profile found.']);
            return;
        }

        $next = Consultation::where('doctor_id', $doctor->id)
            ->whereDate('consultation_date', date('Y-m-d'))
            ->where('status', 'Pending')
            ->orderBy('token_number')
            ->first();

        if ($next) {
            $this->selectConsultation($next->id);
        } else {
            $this->reset(['selectedConsultation', 'lastVisit', 'timeline', 'billStats', 'alerts']);
        }

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Consultation completed.']);
    }

    public function render()
    {
        $doctor = $this->doctor;
        
        if (!$doctor) {
            return view('livewire.doctor.consultation-desk', [
                'queue' => collect(),
                'completed' => collect(),
                'doctor' => null
            ]);
        }

        $queue = Consultation::query()->with(['patient'])
            ->where('doctor_id', '=', $doctor->id)
            ->whereDate('consultation_date', date('Y-m-d'))
            ->whereIn('status', ['Pending', 'Ongoing'])
            ->orderBy('token_number')
            ->get();

        $completed = Consultation::query()->with(['patient'])
            ->where('doctor_id', '=', $doctor->id)
            ->whereDate('consultation_date', date('Y-m-d'))
            ->where(fn($q) => $q->where('status', '=', 'Completed'))
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        return view('livewire.doctor.consultation-desk', [
            'queue' => $queue,
            'completed' => $completed,
            'doctor' => $doctor
        ]);
    }
}
