<?php

namespace App\Livewire\IPD;

use App\Models\Admission;
use App\Models\ClinicalTemplate;
use App\Models\IpdNote;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class IpdNotes extends Component
{
    public Admission $admission;

    public $activeTab = 'doctor';
    public $note_type = 'Doctor';
    public $note_date;
    public $note_content;
    public $editingId = null;
    public $showForm = false;

    public $quickNotes = [];

    protected $rules = [
        'note_type' => 'required|in:Doctor,Nurse,Procedure,Progress,Emergency,Death',
        'note_date' => 'required|date',
        'note_content' => 'required|string',
    ];

    public function mount(Admission $admission)
    {
        $this->admission = $admission;
        $this->note_date = now()->format('Y-m-d\TH:i');
        $this->loadQuickNotes();
    }

    public function loadQuickNotes()
    {
        $templates = ClinicalTemplate::where('type', 'notes')
            ->get();

        $this->quickNotes = [
            'doctor' => $templates->pluck('content')->toArray(),
            'nurse' => $templates->pluck('content')->toArray(),
            'general' => $templates->pluck('content')->toArray(),
        ];
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->note_type = match($tab) {
            'doctor' => 'Doctor',
            'nurse' => 'Nurse',
            'procedure' => 'Procedure',
            'progress' => 'Progress',
            default => 'Doctor',
        };
    }

    public function saveNote()
    {
        $this->validate();

        $user = Auth::user();

        if ($this->editingId) {
            $note = IpdNote::findOrFail($this->editingId);
            if (!$note->isEditable()) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'This note can no longer be edited.']);
                return;
            }
            $note->update([
                'note_type' => $this->note_type,
                'note_date' => $this->note_date,
                'content' => $this->note_content,
            ]);
            $message = 'Note updated successfully';
        } else {
            $doctor = $this->admission->doctor;

            IpdNote::create([
                'admission_id' => $this->admission->id,
                'patient_id' => $this->admission->patient_id,
                'doctor_id' => $doctor?->id,
                'created_by' => $user->id,
                'note_type' => $this->note_type,
                'note_date' => $this->note_date,
                'content' => $this->note_content,
                'is_editable' => true,
            ]);
            $message = 'Note added successfully';
        }

        $this->reset(['note_content', 'editingId', 'showForm']);
        $this->note_date = now()->format('Y-m-d\TH:i');

        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
    }

    public function editNote($noteId)
    {
        $note = IpdNote::findOrFail($noteId);
        if (!$note->isEditable()) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'This note can no longer be edited.']);
            return;
        }

        $this->editingId = $noteId;
        $this->note_type = $note->note_type;
        $this->note_date = $note->note_date->format('Y-m-d\TH:i');
        $this->note_content = $note->content;
        $this->showForm = true;
    }

    public function cancelEdit()
    {
        $this->reset(['editingId', 'note_content', 'showForm']);
        $this->note_date = now()->format('Y-m-d\TH:i');
    }

    public function lockNote($noteId)
    {
        $note = IpdNote::findOrFail($noteId);
        $note->lock();

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Note locked successfully']);
    }

    public function appendQuickNote($note)
    {
        $this->note_content = $this->note_content ? $this->note_content . "\n" . $note : $note;
    }

    public function getDoctorNotesProperty()
    {
        return IpdNote::where('admission_id', $this->admission->id)
            ->where('note_type', 'Doctor')
            ->orderBy('note_date', 'desc')
            ->get();
    }

    public function getNurseNotesProperty()
    {
        return IpdNote::where('admission_id', $this->admission->id)
            ->where('note_type', 'Nurse')
            ->orderBy('note_date', 'desc')
            ->get();
    }

    public function getProcedureNotesProperty()
    {
        return IpdNote::where('admission_id', $this->admission->id)
            ->where('note_type', 'Procedure')
            ->orderBy('note_date', 'desc')
            ->get();
    }

    public function getProgressNotesProperty()
    {
        return IpdNote::where('admission_id', $this->admission->id)
            ->where('note_type', 'Progress')
            ->orderBy('note_date', 'desc')
            ->get();
    }

    public function getAllNotesProperty()
    {
        return IpdNote::where('admission_id', $this->admission->id)
            ->orderBy('note_date', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.ipd.ipd-notes');
    }
}
