<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Patient management
            'view patients', 'create patients', 'update patients', 'delete patients',
            // OPD
            'view opd', 'create opd visits', 'update opd vitals', 'edit case sheets', 'write prescriptions',
            // IPD
            'view ipd', 'admit patients', 'update ipd notes', 'manage medications',
            // Pharmacy
            'view pharmacy', 'dispense medicines', 'manage inventory',
            // Laboratory
            'view lab', 'collect samples', 'enter lab results',
            // Billing
            'view billing', 'create invoices', 'manage payments',
            // Reports
            'view reports',
            // Settings & Master Data
            'manage settings', 'manage master data', 'manage users'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define roles and assign permissions
        $roles = [
            'doctor_owner' => $permissions, // All permissions
            
            'receptionist' => [
                'view patients', 'create patients', 'update patients',
                'view opd', 'create opd visits',
                'view billing', 'create invoices', 'manage payments'
            ],
            
            'nurse' => [
                'view patients',
                'view ipd', 'update ipd notes', 'manage medications',
                'update opd vitals'
            ],
            
            'lab_technician' => [
                'view lab', 'collect samples', 'enter lab results'
            ],
            
            'pharmacist' => [
                'view pharmacy', 'dispense medicines', 'manage inventory'
            ],
            
            'accountant' => [
                'view billing', 'manage payments', 'view reports'
            ]
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($perms);
        }
    }
}
