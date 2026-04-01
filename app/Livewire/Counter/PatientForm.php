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

    #[Validate('required|string|max:255')]
    public $first_name;

    #[Validate('nullable|string|max:255')]
    public $last_name;

    #[Validate('nullable|string|max:255')]
    public $father_name;

    #[Validate('required|string|max:255')]
    public $mother_name;

    #[Validate('required|in:Male,Female,Other')]
    public $gender;

    #[Validate('required|date|before_or_equal:today')]
    public $date_of_birth;

    #[Validate('required|string|max:15')]
    public $phone;

    #[Validate('nullable|string|max:255')]
    public $insurance_provider;

    #[Validate('nullable|string|max:255')]
    public $insurance_policy;

    #[Validate('nullable|date')]
    public $insurance_validity;

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

    #[On('create-patient')]
    public function create($phone = null)
    {
        $this->reset();
        $this->resetValidation();
        $this->isEditing = false;
        
        if ($phone) {
            $this->phone = $phone;
        }

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
        $this->date_of_birth = $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : null;
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
        $this->insurance_provider = $patient->insurance_provider;
        $this->insurance_policy = $patient->insurance_policy;
        $this->insurance_validity = $patient->insurance_validity ? $patient->insurance_validity->format('Y-m-d') : null;

        $this->dispatch('open-modal', name: 'patient-modal');
    }

    public function save(PatientService $service)
    {
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
            'insurance_provider' => $this->insurance_provider,
            'insurance_policy' => $this->insurance_policy,
            'insurance_validity' => $this->insurance_validity,
        ];

        if ($this->isEditing) {
            $patient = Patient::findOrFail($this->patientId);
            $service->update($patient, $data);
            $message = 'Patient details updated successfully!';
        } else {
            $patient = $service->create($data);
            $message = 'Patient registered successfully with UHID!';
            $this->dispatch('patient-registered', $patient->id);
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
