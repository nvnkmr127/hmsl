<?php

namespace App\Livewire\Counter;

use App\Models\Patient;
use App\Services\PatientService;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class PatientForm extends Component
{
    public $isEditing = false;
    public $patientId;
    public $matchedPatientName;
    public $duplicateFound = false;
    public $is_baby_of = false;

    #[Validate('required|string|max:255')]
    public $first_name;

    #[Validate('nullable|string|max:255')]
    public $last_name;

    #[Validate('nullable|string|max:255')]
    public $father_name;

    #[Validate('nullable|string|max:255')]
    public $mother_name;

    #[Validate('required|in:Male,Female,Other')]
    public $gender;

    #[Validate('required|date|before_or_equal:today')]
    public $date_of_birth;

    #[Validate('required|digits:10')]
    public $phone;


    #[Validate('nullable|string|max:10')]
    public $blood_group;

    #[Validate('nullable|string')]
    public $address;

    #[Validate('nullable|string|max:100')]
    public $city;

    #[Validate('nullable|string|max:100')]
    public $state;

    #[Validate('nullable|string|max:10')]
    public $pincode;

    #[Validate('nullable|string|max:255')]
    public $emergency_contact_name;

    #[Validate('nullable|string|max:15')]
    public $emergency_contact_phone;

    #[Validate('nullable|string|max:100')]
    public $marital_status;

    public $is_active = true;

    public function updatedPhone($value)
    {
        $this->matchedPatientName = null;
        if (strlen($value) >= 10) {
            $patients = Patient::where('phone', $value)->latest()->get();
            if ($patients->isNotEmpty()) {
                $lastPatient = $patients->first();
                $names = $patients->pluck('first_name')->unique()->implode(', ');
                $this->matchedPatientName = $names . ($lastPatient->city ? ' · ' . $lastPatient->city : '');
                
                $this->autofillFromRecord($lastPatient);
                $this->checkDuplicate();
            } else {
                $this->resetFields();
            }
        } else {
            $this->resetFields();
        }
    }

    private function autofillFromRecord(Patient $patient)
    {
        $this->address = $patient->address;
        $this->city = $patient->city;
        $this->state = $patient->state;
        $this->pincode = $patient->pincode;
        $this->father_name = $patient->father_name;
        $this->mother_name = $patient->mother_name;
        $this->emergency_contact_name = $patient->emergency_contact_name;
        $this->emergency_contact_phone = $patient->emergency_contact_phone;
        
        // Only autofill identity if not editing
        if (!$this->isEditing) {
            $this->first_name = $patient->first_name;
            $this->last_name = $patient->last_name;
            $this->gender = $patient->gender;
            $this->date_of_birth = $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : null;
        }
    }

    public function updatedMotherName($value)
    {
        if ($this->is_baby_of) {
            $this->first_name = 'B/O ' . ($value ?: '[Mother\'s Name]');
        }
    }

    public function updatedIsBabyOf($value)
    {
        if ($value) {
            $this->first_name = 'B/O ' . ($this->mother_name ?: '[Mother\'s Name]');
        } else {
            // Optional: Clear or keep? Keeping it might be better if they toggled by mistake.
            // But usually, they'd want to type a name now.
            if (str_starts_with($this->first_name, 'B/O ')) {
                $this->first_name = '';
            }
        }
    }

    private function resetFields()
    {
        $this->matchedPatientName = null;
        $this->duplicateFound = false;
        $this->is_baby_of = false;
        $this->reset([
            'first_name', 'last_name', 'father_name', 'mother_name', 
            'gender', 'date_of_birth', 'address', 'city', 'state', 'pincode',
            'emergency_contact_name', 'emergency_contact_phone'
        ]);
    }

    public function updated($property)
    {
        if (in_array($property, ['first_name', 'last_name', 'phone', 'date_of_birth', 'gender'])) {
            $this->checkDuplicate();
        }
    }

    private function checkDuplicate()
    {
        if ($this->isEditing) {
            $this->duplicateFound = false;
            return;
        }

        if ($this->first_name && $this->phone) {
            $this->duplicateFound = Patient::where('first_name', $this->first_name)
                ->where('phone', $this->phone)
                ->when($this->date_of_birth, fn($q) => $q->whereDate('date_of_birth', $this->date_of_birth))
                ->exists();
        } else {
            $this->duplicateFound = false;
        }
    }

    #[On('create-patient')]
    public function create($phone = null, $name = null, $mother_name = null)
    {
        $this->reset();
        $this->resetValidation();
        $this->isEditing = false;
        
        // Handle named array or direct arguments
        if (is_array($phone)) {
            $name = $phone['name'] ?? null;
            $mother_name = $phone['mother_name'] ?? null;
            $phone = $phone['phone'] ?? null;
        }
        
        if ($phone) {
            $this->phone = $phone;
            
            // Auto-fetch details from siblings
            $patients = Patient::where('phone', $phone)->latest()->get();
            if ($patients->isNotEmpty()) {
                $lastPatient = $patients->first();
                $names = $patients->pluck('first_name')->unique()->implode(', ');
                $this->matchedPatientName = $names . ($lastPatient->city ? ' · ' . $lastPatient->city : '');
                
                $this->autofillFromRecord($lastPatient);
            }
        }

        if ($name) {
            $this->first_name = $name;
        }

        if ($mother_name) {
            $this->mother_name = $mother_name;
        }

        $this->checkDuplicate();
        $this->dispatch('open-modal', name: 'patient-modal');
    }

    #[On('edit-patient')]
    public function edit($id)
    {
        $this->resetValidation();
        $this->isEditing = true;
        $this->patientId = $id;
        
        $patient = Patient::findOrFail($id);
        $this->first_name = $patient->first_name;
        $this->last_name = $patient->last_name;
        $this->father_name = $patient->father_name;
        $this->mother_name = $patient->mother_name;
        $this->gender = $patient->gender;
        $this->date_of_birth = $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('Y-m-d') : null;
        $this->phone = $patient->phone;
        $this->blood_group = $patient->blood_group;
        $this->address = $patient->address;
        $this->city = $patient->city;
        $this->state = $patient->state;
        $this->pincode = $patient->pincode;
        $this->emergency_contact_name = $patient->emergency_contact_name;
        $this->emergency_contact_phone = $patient->emergency_contact_phone;
        $this->marital_status = $patient->marital_status;
        $this->is_active = $patient->is_active;

        // Auto-detect B/O status
        $this->is_baby_of = str_starts_with($patient->first_name, 'B/O ');


        $this->dispatch('open-modal', name: 'patient-modal');
    }

    public function save(PatientService $service)
    {
        if ($this->is_baby_of && empty(trim($this->mother_name))) {
            $this->addError('mother_name', 'Mother\'s name is required for unnamed babies.');
            return;
        }

        $this->checkDuplicate();
        if ($this->duplicateFound && !$this->isEditing) {
             $this->addError('first_name', 'A patient with this name and phone already exists.');
             return;
        }

        $this->validate();

        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'father_name' => $this->father_name,
            'mother_name' => $this->mother_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'phone' => $this->phone,
            'blood_group' => $this->blood_group,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'pincode' => $this->pincode,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'marital_status' => $this->marital_status,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $patient = Patient::findOrFail($this->patientId);
            $service->update($patient, $data);
            $message = 'Patient details updated successfully!';
        } else {
            try {
                $patient = $service->create($data);
                $message = 'Patient registered successfully with UHID!';
                $this->dispatch('patient-registered', id: $patient->id);
            } catch (\Exception $e) {
                $this->dispatch('notify', type: 'error', message: $e->getMessage());
                return;
            }
        }

        $this->dispatch('close-modal', name: 'patient-modal');
        $this->dispatch('patient-saved');
        $this->dispatch('notify', type: 'success', message: $message);
    }

    public function render()
    {
        return view('livewire.counter.patient-form');
    }
}
