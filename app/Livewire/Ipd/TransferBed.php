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

    public function cancelTransfer()
    {
        $this->reset(['newBedId', 'reason']);
        $this->newWardId = $this->admission->bed?->ward_id;
        $this->dispatch('close-modal', name: 'transfer-bed-modal');
    }

    public function transfer(\App\Services\IpdService $manager)
    {
        $this->validate([
            'newWardId' => 'required|exists:wards,id',
            'newBedId' => 'required|exists:beds,id',
        ]);

        try {
            $oldBedNumber = $this->admission->bed->bed_number ?? 'Unknown';
            $manager->transferPatient($this->admission, $this->newBedId, $this->reason ?: null);

            $newBed = Bed::find($this->newBedId);
            
            // Log the transfer in clinical notes
            \App\Models\IpdNote::create([
                'admission_id' => $this->admission->id,
                'patient_id' => $this->admission->patient_id,
                'note_type' => 'Progress',
                'note_date' => now(),
                'content' => "BED TRANSFER: Transferred from " . $oldBedNumber . " to " . ($newBed->bed_number ?? 'Unknown') . ". Reason: " . ($this->reason ?: 'Medical/Administrative'),
                'created_by' => auth()->id(),
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Patient transferred to ' . Bed::find($this->newBedId)->bed_number
            ]);

            $this->dispatch('close-modal', name: 'transfer-bed-modal');
            $this->reset(['newBedId', 'reason']);
            $this->newWardId = $newBed->ward_id;
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
