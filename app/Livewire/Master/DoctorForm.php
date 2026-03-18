<?php

namespace App\Livewire\Master;

use App\Models\Doctor;
use App\Models\Department;
use App\Models\User;
use App\Services\DoctorService;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class DoctorForm extends Component
{
    public $isEditing = false;
    public $doctorId;

    #[Validate('required|string|max:255')]
    public $full_name;

    #[Validate('required|exists:departments,id')]
    public $department_id;

    #[Validate('required|string|max:255')]
    public $specialization;

    #[Validate('nullable|string|max:255')]
    public $qualification;

    #[Validate('nullable|string|max:20')]
    public $phone;

    #[Validate('nullable|email|max:255')]
    public $email;

    #[Validate('required|numeric|min:0')]
    public $consultation_fee;

    #[Validate('nullable|string|max:100')]
    public $registration_number;

    #[Validate('nullable|string|max:1000')]
    public $biography;

    public $is_active = true;
    public $user_id;

    public function mount()
    {
        $this->consultation_fee = \App\Models\Setting::get('consultation_fee_default', 500);
    }

    #[On('edit-doctor')]
    public function edit($id)
    {
        $this->isEditing = true;
        $this->doctorId = $id;
        
        $doctor = Doctor::findOrFail($id);
        $this->full_name = $doctor->full_name;
        $this->department_id = $doctor->department_id;
        $this->specialization = $doctor->specialization;
        $this->qualification = $doctor->qualification;
        $this->phone = $doctor->phone;
        $this->email = $doctor->email;
        $this->consultation_fee = $doctor->consultation_fee;
        $this->registration_number = $doctor->registration_number;
        $this->biography = $doctor->biography;
        $this->is_active = $doctor->is_active;
        $this->user_id = $doctor->user_id;

        $this->dispatch('open-modal', ['name' => 'doctor-modal']);
    }

    #[On('create-doctor')]
    public function create()
    {
        $this->reset(['full_name', 'department_id', 'specialization', 'qualification', 'phone', 'email', 'registration_number', 'biography', 'is_active', 'doctorId', 'isEditing', 'user_id']);
        $this->consultation_fee = \App\Models\Setting::get('consultation_fee_default', 500);
        $this->resetValidation();
        $this->dispatch('open-modal', ['name' => 'doctor-modal']);
    }

    public function save(DoctorService $service)
    {
        $this->validate();

        $data = [
            'full_name' => $this->full_name,
            'department_id' => $this->department_id,
            'specialization' => $this->specialization,
            'qualification' => $this->qualification,
            'phone' => $this->phone,
            'email' => $this->email,
            'consultation_fee' => $this->consultation_fee,
            'registration_number' => $this->registration_number,
            'biography' => $this->biography,
            'is_active' => $this->is_active,
            'user_id' => $this->user_id,
        ];

        if ($this->isEditing) {
            $doctor = Doctor::findOrFail($this->doctorId);
            $service->update($doctor, $data);
        } else {
            $service->create($data);
        }

        $this->dispatch('close-modal', ['name' => 'doctor-modal']);
        $this->dispatch('doctor-updated');
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->isEditing ? 'Doctor details updated!' : 'New doctor added!'
        ]);
    }

    public function render()
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('livewire.master.doctor-form', [
            'departments' => $departments
        ]);
    }
}
