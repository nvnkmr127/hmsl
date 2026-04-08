<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\HospitalOwner;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DoctorService
{
    public function getAll()
    {
        return Doctor::with('department')->latest()->get();
    }

    public function create(array $data)
    {
        $this->validateDoctorAccountRules($data);
        $doctor = Doctor::create($data);

        if (!empty($data['user_id'])) {
            HospitalOwner::setOwnerDoctor($doctor);
        }

        return $doctor;
    }

    public function update(Doctor $doctor, array $data)
    {
        $this->validateDoctorAccountRules($data, $doctor);
        $doctor->update($data);

        if (!empty($data['user_id'])) {
            HospitalOwner::setOwnerDoctor($doctor);
        }

        return $doctor;
    }

    public function toggleActive(Doctor $doctor)
    {
        $doctor->update(['is_active' => !$doctor->is_active]);
        return $doctor;
    }

    public function delete(Doctor $doctor)
    {
        return $doctor->delete();
    }

    private function validateDoctorAccountRules(array $data, ?Doctor $doctor = null): void
    {
        $userId = $data['user_id'] ?? null;
        if (!$userId) {
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            throw ValidationException::withMessages([
                'user_id' => 'Invalid user selected.',
            ]);
        }

        if (!$user->hasRole('doctor_owner')) {
            throw ValidationException::withMessages([
                'user_id' => 'Only the hospital owner can have a doctor login.',
            ]);
        }

        $existingLinkedDoctor = Doctor::query()
            ->whereNotNull('user_id')
            ->when($doctor, fn($q) => $q->where('id', '!=', $doctor->id))
            ->first();

        if ($existingLinkedDoctor) {
            throw ValidationException::withMessages([
                'user_id' => 'A doctor login already exists. Only one doctor account is allowed.',
            ]);
        }

        $ownerDoctor = HospitalOwner::ownerDoctor();
        if ($ownerDoctor && $doctor && (int) $ownerDoctor->id !== (int) $doctor->id) {
            throw ValidationException::withMessages([
                'user_id' => 'This hospital already has an owner doctor.',
            ]);
        }
    }
}
