<div>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Clinical Notes</h3>
        @unless($admission->status === 'Discharged')
            <button wire:click="$toggle('showForm')" class="btn btn-primary text-xs" wire:loading.attr="disabled">
                <svg wire:loading class="w-4 h-4 animate-spin mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Add Note
            </button>
        @endunless
    </div>

    <div class="mb-4 border-b border-gray-100 dark:border-gray-800">
        <nav class="flex gap-1">
            <button wire:click="setTab('doctor')" class="px-3 py-2 text-xs font-bold border-b-2 {{ $activeTab === 'doctor' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                Doctor ({{ $this->doctorNotes->count() }})
            </button>
            <button wire:click="setTab('nurse')" class="px-3 py-2 text-xs font-bold border-b-2 {{ $activeTab === 'nurse' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                Nurse ({{ $this->nurseNotes->count() }})
            </button>
            <button wire:click="setTab('procedure')" class="px-3 py-2 text-xs font-bold border-b-2 {{ $activeTab === 'procedure' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                Procedure ({{ $this->procedureNotes->count() }})
            </button>
            <button wire:click="setTab('progress')" class="px-3 py-2 text-xs font-bold border-b-2 {{ $activeTab === 'progress' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                Progress ({{ $this->progressNotes->count() }})
            </button>
        </nav>
    </div>

    @if($showForm)
        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl mb-4">
            <div class="flex items-center gap-4 mb-4">
                <div class="flex-1">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Note Type</label>
                    <select wire:model="note_type" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm">
                        <option value="Doctor">Doctor Note</option>
                        <option value="Nurse">Nursing Note</option>
                        <option value="Procedure">Procedure Note</option>
                        <option value="Progress">Progress Note</option>
                        <option value="Emergency">Emergency Note</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Date & Time</label>
                    <input type="datetime-local" wire:model="note_date" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm">
                </div>
            </div>

            @if(count($this->quickNotes[$activeTab] ?? []))
                <div class="mb-3">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-2 block">Quick Notes</label>
                    <div class="flex flex-wrap gap-1">
                        @foreach($this->quickNotes[$activeTab] as $quickNote)
                            <button wire:click="appendQuickNote('{{ $quickNote }}')" class="px-2 py-1 text-xs bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30">
                                {{ Str::limit($quickNote, 30) }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mb-3">
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Note Content</label>
                <textarea wire:model="note_content" rows="4" class="w-full rounded-lg border-gray-200 dark:border-gray-700 text-sm" placeholder="Enter clinical note..."></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button wire:click="cancelEdit" class="btn btn-secondary text-xs">Cancel</button>
                <button wire:click="saveNote" class="btn btn-primary text-xs" wire:loading.attr="disabled">
                    <svg wire:loading class="w-4 h-4 animate-spin mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Save Note
                </button>
            </div>
        </div>
    @endif

    <div class="space-y-3">
        @forelse($activeTab === 'doctor' ? $this->doctorNotes : ($activeTab === 'nurse' ? $this->nurseNotes : ($activeTab === 'procedure' ? $this->procedureNotes : $this->progressNotes)) as $note)
            <div class="p-4 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $note->note_type === 'Doctor' ? 'bg-indigo-100 text-indigo-700' : ($note->note_type === 'Nurse' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ $note->note_type }}
                        </span>
                        <span class="text-xs text-gray-500">{{ $note->note_date->format('d M Y, h:i A') }}</span>
                        @if($note->is_locked)
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        @endif
                    </div>
                    @if($note->isEditable() && $admission->status !== 'Discharged')
                        <div class="flex items-center gap-1">
                            <button wire:click="editNote({{ $note->id }})" class="p-1 text-gray-400 hover:text-indigo-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button wire:click="lockNote({{ $note->id }})" class="p-1 text-gray-400 hover:text-amber-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </button>
                        </div>
                    @endif
                </div>
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $note->content }}</p>
                @if($note->creator)
                    <p class="text-xs text-gray-400 mt-2">Recorded by: {{ $note->creator->name }}</p>
                @endif
            </div>
        @empty
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-sm">No {{ $activeTab }} notes recorded yet.</p>
            </div>
        @endforelse
    </div>
</div>
