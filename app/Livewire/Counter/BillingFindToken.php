<?php

namespace App\Livewire\Counter;

use App\Models\Consultation;
use Livewire\Component;

class BillingFindToken extends Component
{
    public string $search = '';

    public function selectConsultation(int $consultationId): void
    {
        $consultation = Consultation::findOrFail($consultationId);

        $this->dispatch('generate-bill', $consultationId);
        $this->dispatch('close-modal', name: 'billing-find-token-modal');
        $this->dispatch('close-modal', name: 'billing-create-modal');
    }

    public function render()
    {
        $query = Consultation::with(['patient', 'doctor.department'])
            ->has('patient')
            ->orderByDesc('consultation_date');
        $query->whereDate('consultation_date', '>=', now()->subDays(30)->toDateString());

        if ($this->search) {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('token_number', 'like', $term)
                    ->orWhereHas('patient', function ($pq) use ($term) {
                        $pq->where('uhid', 'like', $term)
                            ->orWhere('first_name', 'like', $term)
                            ->orWhere('last_name', 'like', $term)
                            ->orWhere('phone', 'like', $term);
                    })
                    ->orWhereHas('doctor', function ($dq) use ($term) {
                        $dq->where('full_name', 'like', $term);
                    });
            });
        }

        $consultations = $query->limit(10)->get();

        return view('livewire.counter.billing-find-token', [
            'consultations' => $consultations,
        ]);
    }
}
