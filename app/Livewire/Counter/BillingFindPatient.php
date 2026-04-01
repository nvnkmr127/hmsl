<?php

namespace App\Livewire\Counter;

use App\Models\Patient;
use Livewire\Component;

class BillingFindPatient extends Component
{
    public string $search = '';

    public function selectPatient(int $patientId): void
    {
        $this->dispatch('generate-bill-for-patient', $patientId);
        $this->dispatch('close-modal', ['name' => 'billing-find-patient-modal']);
        $this->dispatch('close-modal', ['name' => 'billing-create-modal']);
    }

    public function render()
    {
        $patients = Patient::query()
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('uhid', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('livewire.counter.billing-find-patient', [
            'patients' => $patients,
        ]);
    }
}

