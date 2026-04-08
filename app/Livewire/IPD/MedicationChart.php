<?php

namespace App\Livewire\IPD;

use App\Models\Admission;
use App\Models\IpdMedicationChart;
use App\Models\Medicine;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MedicationChart extends Component
{
    public Admission $admission;

    public $medicine_name;
    public $medicine_id;
    public $dosage;
    public $frequency;
    public $route = 'Oral';
    public $start_date;
    public $end_date;
    public $instructions;

    public $searchMedicine = '';
    public $showMedicineSearch = false;

    public $showForm = false;
    public $editingId = null;
    public $stopReason = '';
    public $showStopModal = false;
    public $stoppingId = null;

    public $activeTab = 'active';

    protected $rules = [
        'medicine_name' => 'required|string',
        'medicine_id' => 'nullable|exists:medicines,id',
        'dosage' => 'nullable|string',
        'frequency' => 'nullable|string',
        'route' => 'nullable|string',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'instructions' => 'nullable|string',
    ];

    public function mount(Admission $admission)
    {
        $this->admission = $admission;
        $this->start_date = now()->format('Y-m-d');
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

    public function selectMedicine($medicineId)
    {
        $medicine = Medicine::findOrFail($medicineId);
        $this->medicine_id = $medicine->id;
        $this->medicine_name = $medicine->name;
        $this->searchMedicine = '';
        $this->showMedicineSearch = false;
    }

    public function save()
    {
        $this->validate();

        $doctor = $this->admission->doctor;

        if ($this->editingId) {
            $med = IpdMedicationChart::findOrFail($this->editingId);
            $med->update([
                'medicine_name' => $this->medicine_name,
                'medicine_id' => $this->medicine_id,
                'dosage' => $this->dosage,
                'frequency' => $this->frequency,
                'route' => $this->route,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'instructions' => $this->instructions,
            ]);
            $message = 'Medication updated';
        } else {
            IpdMedicationChart::create([
                'admission_id' => $this->admission->id,
                'patient_id' => $this->admission->patient_id,
                'medicine_id' => $this->medicine_id,
                'prescribed_by' => Auth::id(),
                'doctor_id' => $doctor?->id,
                'medicine_name' => $this->medicine_name,
                'dosage' => $this->dosage,
                'frequency' => $this->frequency,
                'route' => $this->route,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'instructions' => $this->instructions,
                'status' => 'Active',
            ]);
            $message = 'Medication added';
        }

        $this->resetForm();
        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
    }

    public function editMedication($id)
    {
        $med = IpdMedicationChart::findOrFail($id);
        $this->editingId = $id;
        $this->medicine_id = $med->medicine_id;
        $this->medicine_name = $med->medicine_name;
        $this->dosage = $med->dosage;
        $this->frequency = $med->frequency;
        $this->route = $med->route;
        $this->start_date = $med->start_date->format('Y-m-d');
        $this->end_date = $med->end_date?->format('Y-m-d');
        $this->instructions = $med->instructions;
        $this->showForm = true;
    }

    public function confirmStop($id)
    {
        $this->stoppingId = $id;
        $this->stopReason = '';
        $this->showStopModal = true;
    }

    public function stopMedication()
    {
        if (!$this->stopReason) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Please provide a reason for stopping.']);
            return;
        }

        $med = IpdMedicationChart::findOrFail($this->stoppingId);
        $med->stop($this->stopReason, Auth::user());

        $this->showStopModal = false;
        $this->stoppingId = null;
        $this->stopReason = '';

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Medication stopped']);
    }

    public function markDispensed($id)
    {
        $med = IpdMedicationChart::findOrFail($id);
        $med->markDispensed();

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Marked as dispensed']);
    }

    public function resetForm()
    {
        $this->reset(['medicine_name', 'medicine_id', 'dosage', 'frequency', 'route', 'end_date', 'instructions', 'editingId']);
        $this->start_date = now()->format('Y-m-d');
        $this->route = 'Oral';
        $this->showForm = false;
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function getActiveMedicationsProperty()
    {
        return IpdMedicationChart::where('admission_id', $this->admission->id)
            ->where('status', 'Active')
            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function getStoppedMedicationsProperty()
    {
        return IpdMedicationChart::where('admission_id', $this->admission->id)
            ->where('status', 'Stopped')
            ->orderBy('stopped_at', 'desc')
            ->get();
    }

    public function getCompletedMedicationsProperty()
    {
        return IpdMedicationChart::where('admission_id', $this->admission->id)
            ->where('status', 'Completed')
            ->orderBy('end_date', 'desc')
            ->get();
    }

    public function getAllMedicationsProperty()
    {
        return IpdMedicationChart::where('admission_id', $this->admission->id)
            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function render()
    {
        $medicines = $this->searchMedicines();
        $frequencyOptions = ['OD', 'BD', 'TDS', 'QID', 'SOS', 'PRN', 'Stat', 'Every 4 hours', 'Every 6 hours', 'Every 8 hours', 'Every 12 hours'];
        $routeOptions = ['Oral', 'IV', 'IM', 'SC', 'Inhalation', 'Topical', 'Rectal', 'Sublingual', 'Nasal', 'Otic', 'Ophthalmic'];

        return view('livewire.ipd.medication-chart', [
            'medicines' => $medicines,
            'frequencyOptions' => $frequencyOptions,
            'routeOptions' => $routeOptions,
        ]);
    }
}
