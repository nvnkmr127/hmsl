<?php

namespace App\Livewire\Master;

use App\Models\Department;
use App\Services\DepartmentService;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class DepartmentForm extends Component
{
    public $isEditing = false;
    public $departmentId;

    #[Validate('nullable|string|max:50')]
    public $code;

    #[Validate('required|string|max:255')]
    public $name;


    #[Validate('nullable|string|max:1000')]
    public $description;

    public $is_active = true;

    #[On('edit-department')]
    public function edit($id)
    {
        $this->isEditing = true;
        $this->departmentId = $id;
        
        $department = Department::findOrFail($id);
        $this->code = $department->code;
        $this->name = $department->name;
        $this->description = $department->description;
        $this->is_active = $department->is_active;


        $this->dispatch('open-modal', name: 'department-modal');
    }

    #[On('create-department')]
    public function create()
    {
        $this->reset('code', 'name', 'description', 'is_active', 'departmentId', 'isEditing');

        $this->resetValidation();
        $this->dispatch('open-modal', name: 'department-modal');
    }

    public function save(DepartmentService $service)
    {
        $rules = [
            'code' => 'nullable|string|max:50|unique:departments,code,' . $this->departmentId,
            'name' => 'required|string|max:255|unique:departments,name,' . $this->departmentId,
            'description' => 'nullable|string|max:1000',
        ];

        $this->validate($rules);


        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];


        if ($this->isEditing) {
            $department = Department::findOrFail($this->departmentId);
            $service->update($department, $data);
        } else {
            $service->create($data);
        }

        $this->dispatch('close-modal', name: 'department-modal');
        $this->dispatch('department-updated');
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->isEditing ? 'Department updated!' : 'Department created!'
        ]);
    }

    public function render()
    {
        return view('livewire.master.department-form');
    }
}
