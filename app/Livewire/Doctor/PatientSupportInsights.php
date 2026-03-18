<?php

namespace App\Livewire\Doctor;

use App\Models\Bill;
use App\Models\Patient;
use Livewire\Component;

class PatientSupportInsights extends Component
{
    public $patientId;
    public $billStats = [];
    public $insurance = [];

    public function mount($patientId)
    {
        $this->patientId = $patientId;
    }

    public function render()
    {
        $id = $this->patientId;
        $patient = Patient::find($id);
        
        $thirtyDaysAgo = now()->subDays(30);
        $this->billStats = [
            'thirty_days' => Bill::where('patient_id', $id)->where('created_at', '>=', $thirtyDaysAgo)->sum('total_amount'),
            'last_bill' => Bill::where('patient_id', $id)->latest()->first(),
            'today_bill' => Bill::where('patient_id', $id)->whereDate('created_at', date('Y-m-d'))->first()
        ];

        $this->insurance = [
            'provider' => $patient->insurance_provider,
            'policy' => $patient->insurance_policy,
            'validity' => $patient->insurance_validity
        ];

        return view('livewire.doctor.patient-support-insights');
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="space-y-8 animate-pulse">
            <div class="p-8 bg-gray-100/50 dark:bg-gray-800/20 rounded-[2.5rem] border border-gray-100 dark:border-gray-800 h-40">
                <div class="h-4 w-20 bg-gray-200 dark:bg-gray-700 rounded mb-4"></div>
                <div class="h-8 w-40 bg-gray-200 dark:bg-gray-700 rounded mb-6"></div>
                <div class="h-10 w-full bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
            <div class="p-8 bg-gray-100/30 dark:bg-gray-800/10 rounded-[2.5rem] border border-gray-100 dark:border-gray-800 h-32">
                 <div class="h-4 w-20 bg-gray-200 dark:bg-gray-700 rounded mb-4"></div>
                 <div class="h-6 w-32 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
        </div>
        HTML;
    }
}
