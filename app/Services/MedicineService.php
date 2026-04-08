<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineStockTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MedicineService
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
        return $this->adjustStock($medicine, $quantity, 'adjustment');
    }

    public function adjustStock(
        Medicine $medicine,
        int $quantityChange,
        string $type,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): Medicine {
        return DB::transaction(function () use ($medicine, $quantityChange, $type, $referenceType, $referenceId, $notes) {
            $locked = Medicine::query()->whereKey($medicine->id)->lockForUpdate()->firstOrFail();
            $nextStock = (int) $locked->stock_quantity + (int) $quantityChange;
            if ($nextStock < 0) {
                throw new \RuntimeException('Insufficient stock for this change.');
            }

            $locked->stock_quantity = $nextStock;
            $locked->save();

            MedicineStockTransaction::create([
                'medicine_id' => $locked->id,
                'quantity_change' => $quantityChange,
                'type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);

            if ((int) $locked->stock_quantity <= (int) $locked->min_stock_level) {
                event(new \App\Events\Pharmacy\MedicineLowStock($locked));
            }

            return $locked;
        });
    }
}
