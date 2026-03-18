<?php

namespace App\Services;

use App\Models\Department;

class DepartmentService
{
    public function getAll()
    {
        return Department::latest()->get();
    }

    public function create(array $data)
    {
        return Department::create($data);
    }

    public function update(Department $department, array $data)
    {
        $department->update($data);
        return $department;
    }

    public function toggleActive(Department $department)
    {
        $department->update(['is_active' => !$department->is_active]);
        return $department;
    }

    public function delete(Department $department)
    {
        return $department->delete();
    }
}
