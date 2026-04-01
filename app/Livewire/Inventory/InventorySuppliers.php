<?php

namespace App\Livewire\Inventory;

use App\Models\InventorySupplier;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class InventorySuppliers extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $name, $contact_person, $phone, $email, $address;
    public $selectedSupplierId;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreate()
    {
        $this->reset(['name', 'contact_person', 'phone', 'email', 'address', 'selectedSupplierId']);
        $this->dispatch('open-modal', ['name' => 'supplier-modal']);
    }

    public function edit($id)
    {
        $supplier = InventorySupplier::findOrFail($id);
        $this->selectedSupplierId = $id;
        $this->name = $supplier->name;
        $this->contact_person = $supplier->contact_person;
        $this->phone = $supplier->phone;
        $this->email = $supplier->email;
        $this->address = $supplier->address;
        $this->dispatch('open-modal', ['name' => 'supplier-modal']);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        try {
            $data = [
                'name' => $this->name,
                'contact_person' => $this->contact_person,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
            ];

            if ($this->selectedSupplierId) {
                InventorySupplier::findOrFail($this->selectedSupplierId)->update($data);
                $msg = 'Supplier updated successfully!';
            } else {
                InventorySupplier::create($data);
                $msg = 'Supplier added successfully!';
            }

            $this->dispatch('close-modal', ['name' => 'supplier-modal']);
            $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
            $this->reset();
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Operation failed. Please try again.']);
        }
    }

    public function delete($id)
    {
        try {
            InventorySupplier::findOrFail($id)->delete();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Supplier removed.']);
        } catch (Throwable $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Cannot delete supplier with active transactions.']);
        }
    }

    public function render()
    {
        $suppliers = InventorySupplier::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('contact_person', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.inventory.inventory-suppliers', compact('suppliers'));
    }
}
