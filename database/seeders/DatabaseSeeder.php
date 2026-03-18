<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SettingsSeeder::class,
            AdminUserSeeder::class,
            MasterDataSeeder::class,
            PatientSeeder::class,
            OpdSeeder::class,
        ]);
    }
}
