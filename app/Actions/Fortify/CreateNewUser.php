<?php

namespace App\Actions\Fortify;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\HospitalOwner;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        if (User::query()->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Registration is disabled. Please contact the hospital owner.',
            ]);
        }

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        $user->assignRole('doctor_owner');

        $department = Department::firstOrCreate(
            ['name' => 'General'],
            ['description' => 'Default department']
        );

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'full_name' => $user->name,
            'specialization' => 'General',
            'consultation_fee' => (float) Setting::get('consultation_fee_default', 500),
            'is_active' => true,
        ]);

        HospitalOwner::setOwnerDoctor($doctor);

        return $user;
    }
}
