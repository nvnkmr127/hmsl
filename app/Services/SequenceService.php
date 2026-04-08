<?php

namespace App\Services;

use App\Models\NumberSequence;
use Illuminate\Support\Facades\DB;

class SequenceService
{
    public function next(string $name, ?string $scope = null, ?callable $initialCurrentValue = null): int
    {
        return DB::transaction(function () use ($name, $scope, $initialCurrentValue) {
            $sequence = NumberSequence::query()
                ->where('name', $name)
                ->where('scope', $scope)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                $initial = 0;
                if ($initialCurrentValue) {
                    $initial = (int) $initialCurrentValue();
                    $initial = max(0, $initial);
                }

                $sequence = NumberSequence::create([
                    'name' => $name,
                    'scope' => $scope,
                    'current_value' => $initial,
                ]);

                $sequence = NumberSequence::query()
                    ->whereKey($sequence->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $sequence->current_value = (int) $sequence->current_value + 1;
            $sequence->save();

            return (int) $sequence->current_value;
        }, 5);
    }
}
