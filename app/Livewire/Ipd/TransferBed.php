<?php

namespace App\Livewire\Ipd;

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

        try {
            DB::transaction(function () {
                // Check if patient is still admitted
                if ($this->admission->status !== 'Admitted') {
                    throw new \RuntimeException('Only admitted patients can be transferred.');
                }

                $oldBed = $this->admission->bed;
                $newBed = Bed::query()->lockForUpdate()->findOrFail($this->newBedId);
                
                if (!$newBed->is_available) {
                    throw new \RuntimeException('Selected bed is no longer available.');
                }

                if ($oldBed) {
                    $oldBed->update(['is_available' => true]);
                }

                $newBed->update(['is_available' => false]);

                $this->admission->update([
                    'bed_id' => $this->newBedId,
                ]);

                // Log the transfer in clinical notes
                \App\Models\IpdNote::create([
                    'admission_id' => $this->admission->id,
                    'patient_id' => $this->admission->patient_id,
                    'note_type' => 'Progress',
                    'note_date' => now(),
                    'content' => "BED TRANSFER: Transferred from " . ($oldBed->bed_number ?? 'Unknown') . " to " . $newBed->bed_number . ". Reason: " . ($this->reason ?: 'Medical/Administrative'),
                    'created_by' => auth()->id(),
                ]);
            });

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Patient transferred to ' . Bed::find($this->newBedId)->bed_number
            ]);

            $this->showModal = false;
            $this->reason = '';
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Transfer failed.']);
        }
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
