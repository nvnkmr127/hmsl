<?php

namespace App\Services;

use App\Models\Medicine;

class MedicineManager
{
    public function getAll()
    {
        return Medicine::latest()->get();
    }

    public function create(array $data)
    {
        return Medicine::create($data);
    }

    public function update(Medicine $medicine, array $data)
    {
        $medicine->update($data);
        return $medicine;
    }

    public function toggleActive(Medicine $medicine)
    {
        $medicine->update(['is_active' => !$medicine->is_active]);
        return $medicine;
    }

    public function delete(Medicine $medicine)
    {
        return $medicine->delete();
    }

    public function updateStock(Medicine $medicine, int $quantity)
    {
        $medicine->increment('stock_quantity', $quantity);
        return $medicine;
    }
}
