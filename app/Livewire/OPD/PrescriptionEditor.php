<?php

namespace App\Livewire\OPD;

use App\Models\Consultation;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PrescriptionEditor extends Component
{
    public Consultation $consultation;

    public $medicines = [];
    public $searchMedicine = '';
    public $selectedMedicineId;
    public $selectedMedicineName;
    public $dosage = '';
    public $frequency = '';
    public $duration = '';
    public $route = 'Oral';
    public $instructions = '';

    public $showSearch = false;

    public function mount(Consultation $consultation)
    {
        $this->consultation = $consultation;
        $this->loadMedicines();
    }

    public function loadMedicines()
    {
        $prescription = Prescription::where('consultation_id', $this->consultation->id)->first();

        if ($prescription) {
            $this->medicines = PrescriptionItem::where('prescription_id', $prescription->id)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'medicine_id' => $item->medicine_id,
                        'medicine_name' => $item->medicine_name,
                        'dosage' => $item->dosage,
                        'frequency' => $item->frequency,
                        'duration' => $item->duration,
                        'route' => $item->route,
                        'instructions' => $item->instructions,
                        'quantity' => $item->quantity,
                    ];
                })
                ->toArray();
        }
    }

    public function searchMedicines()
    {
        if (strlen($this->searchMedicine) < 2) {
            return collect();
        }

        return Medicine::where('name', 'like', '%' . $this->searchMedicine . '%')
            ->where('is_active', true)
            ->limit(10)
            ->get();
    }

    public function selectMedicine($medicineId, $medicineName)
    {
        $this->selectedMedicineId = $medicineId;
        $this->selectedMedicineName = $medicineName;
        $this->searchMedicine = '';
        $this->showSearch = false;
    }

    public function addMedicine()
    {
        if (!$this->selectedMedicineName) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Please select a medicine']);
            return;
        }

        $this->medicines[] = [
            'id' => null,
            'medicine_id' => $this->selectedMedicineId,
            'medicine_name' => $this->selectedMedicineName,
            'dosage' => $this->dosage,
            'frequency' => $this->frequency,
            'duration' => $this->duration,
            'route' => $this->route,
            'instructions' => $this->instructions,
            'quantity' => $this->calculateQuantity(),
        ];

        $this->resetMedicineForm();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Medicine added']);
    }

    public function removeMedicine($index)
    {
        unset($this->medicines[$index]);
        $this->medicines = array_values($this->medicines);
    }

    public function calculateQuantity()
    {
        $qty = 1;
        $duration = $this->duration;

        if (preg_match('/(\d+)\s*day/i', $duration, $matches)) {
            $days = (int) $matches[1];
        } elseif (preg_match('/(\d+)\s*week/i', $duration, $matches)) {
            $days = (int) $matches[1] * 7;
        } elseif (preg_match('/(\d+)\s*month/i', $duration, $matches)) {
            $days = (int) $matches[1] * 30;
        } else {
            $days = 7;
        }

        $freqMultiplier = match ($this->frequency) {
            'OD', 'Once daily' => 1,
            'BD', 'Twice daily' => 2,
            'TDS', 'Three times daily' => 3,
            'QID', 'Four times daily' => 4,
            'SOS', 'PRN' => 1,
            default => 1,
        };

        return $days * $freqMultiplier;
    }

    public function resetMedicineForm()
    {
        $this->selectedMedicineId = null;
        $this->selectedMedicineName = '';
        $this->dosage = '';
        $this->frequency = '';
        $this->duration = '';
        $this->route = 'Oral';
        $this->instructions = '';
    }

    public function savePrescription()
    {
        if (empty($this->medicines)) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'No medicines to save']);
            return;
        }

        $prescription = Prescription::updateOrCreate(
            ['consultation_id' => $this->consultation->id],
            [
                'patient_id' => $this->consultation->patient_id,
                'doctor_id' => $this->consultation->doctor_id,
                'consultation_id' => $this->consultation->id,
                'created_by' => Auth::id(),
            ]
        );

        PrescriptionItem::where('prescription_id', $prescription->id)->delete();

        foreach ($this->medicines as $med) {
            PrescriptionItem::create([
                'prescription_id' => $prescription->id,
                'medicine_id' => $med['medicine_id'],
                'medicine_name' => $med['medicine_name'],
                'dosage' => $med['dosage'],
                'frequency' => $med['frequency'],
                'duration' => $med['duration'],
                'route' => $med['route'],
                'instructions' => $med['instructions'],
                'quantity' => $med['quantity'],
            ]);
        }

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Prescription saved successfully']);
    }

    public function render()
    {
        $medicines = $this->searchMedicines();
        $frequencyOptions = ['OD', 'BD', 'TDS', 'QID', 'SOS', 'PRN', 'Stat', 'Every 4 hours', 'Every 6 hours', 'Every 8 hours'];
        $routeOptions = ['Oral', 'IV', 'IM', 'SC', 'Inhalation', 'Topical', 'Rectal', 'Sublingual', 'Nasal', 'Otic', 'Ophthalmic'];

        return view('livewire.opd.prescription-editor', [
            'medicines' => $medicines,
            'frequencyOptions' => $frequencyOptions,
            'routeOptions' => $routeOptions,
        ]);
    }
}
