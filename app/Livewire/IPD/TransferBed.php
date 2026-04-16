<?php

namespace App\Livewire\IPD;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Ward;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TransferBed extends Component
{
    public Admission $admission;

    public $newWardId;
    public $newBedId;
    public $reason;

    public $showModal = false;

    public function mount(Admission $admission)
    {
        $this->admission = $admission;
        $this->newWardId = $admission->bed?->ward_id;
    }

    public function transfer()
    {
        $this->validate([
            'newWardId' => 'required|exists:wards,id',
            'newBedId' => 'required|exists:beds,id',
        ]);

        DB::transaction(function () {
            $oldBed = $this->admission->bed;
            if ($oldBed) {
                $oldBed->update(['is_available' => true]);
            }

            $newBed = Bed::findOrFail($this->newBedId);
            if (!$newBed->is_available) {
                throw new \RuntimeException('Selected bed is no longer available.');
            }

            $newBed->update(['is_available' => false]);

            $this->admission->update([
                'bed_id' => $this->newBedId,
            ]);
        });

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Patient transferred to ' . Bed::find($this->newBedId)->bed_number
        ]);

        $this->showModal = false;
        $this->reason = '';
    }

    public function getAvailableBedsProperty()
    {
        if (!$this->newWardId) return collect();
        return Bed::where('ward_id', $this->newWardId)
            ->where('is_available', true)
            ->orWhere('id', $this->admission->bed_id)
            ->get();
    }

    public function render()
    {
        return view('livewire.ipd.transfer-bed', [
            'wards' => Ward::all(),
        ]);
    }
}
