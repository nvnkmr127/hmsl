<?php

namespace App\Livewire\Master;

use App\Models\ClinicalTemplate;
use Livewire\Component;
use Livewire\Attributes\On;

class ClinicalTemplateForm extends Component
{
    public $isEditing = false;
    public $templateId;
    public $type = 'reason';
    public $content;

    #[On('create-template')]
    public function create()
    {
        $this->reset(['content', 'templateId', 'isEditing']);
        $this->resetValidation();
        $this->dispatch('open-modal', name: 'template-modal');
    }

    #[On('edit-template')]
    public function edit($id)
    {
        $this->resetValidation();
        $this->isEditing = true;
        $this->templateId = $id;
        
        $template = ClinicalTemplate::findOrFail($id);
        $this->type = $template->type;
        $this->content = $template->content;

        $this->dispatch('open-modal', name: 'template-modal');
    }

    public function save()
    {
        $this->validate([
            'type' => 'required|in:reason,notes',
            'content' => 'required|string|max:500',
        ]);

        if ($this->isEditing) {
            $template = ClinicalTemplate::findOrFail($this->templateId);
            $template->update([
                'type' => $this->type,
                'content' => $this->content,
            ]);
            $message = 'Template updated successfully!';
        } else {
            ClinicalTemplate::create([
                'type' => $this->type,
                'content' => $this->content,
            ]);
            $message = 'Template created successfully!';
        }

        $this->dispatch('close-modal', name: 'template-modal');
        $this->dispatch('template-saved');
        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
    }

    public function render()
    {
        return view('livewire.master.clinical-template-form');
    }
}
