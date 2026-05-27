<?php

namespace Tests\Feature;

use App\Models\InventoryCategory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_inventory_category_with_auto_generated_slug_and_description(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('pharmacist');

        Livewire::actingAs($user)
            ->test('master.inventory-category-form')
            ->set('name', 'NICU')
            ->set('description', 'Neonatal Intensive Care Unit Supplies')
            ->call('save');

        $this->assertDatabaseHas('inventory_categories', [
            'name' => 'NICU',
            'slug' => 'nicu',
            'description' => 'Neonatal Intensive Care Unit Supplies',
        ]);
    }

    public function test_can_update_inventory_category_name_slug_and_description(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('pharmacist');

        $category = InventoryCategory::create([
            'name' => 'NICU Old',
            'slug' => 'nicu-old',
            'description' => 'Old description',
        ]);

        Livewire::actingAs($user)
            ->test('master.inventory-category-form')
            ->call('edit', $category->id)
            ->set('name', 'NICU New')
            ->set('description', 'New description')
            ->call('save');

        $this->assertDatabaseHas('inventory_categories', [
            'id' => $category->id,
            'name' => 'NICU New',
            'slug' => 'nicu-new',
            'description' => 'New description',
        ]);
    }
}
