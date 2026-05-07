<div>
    <x-modal name="patient-modal" :title="$isEditing ? 'Update Patient Information' : 'New Patient Registration'" width="3xl">
        <form wire:submit="save" class="space-y-8 p-1">
            <!-- Identity Section -->
            <div class="space-y-6">
                <!-- Child Full Name (Prominent) -->
                <div x-data="{}" x-init="$nextTick(() => $refs.firstName.focus())" class="grid grid-cols-1 gap-4">
                    <div class="p-6 rounded-ultra bg-indigo-50/50 dark:bg-indigo-950/20 border-2 border-indigo-100/50 dark:border-indigo-900/30 group transition-all focus-within:border-indigo-500">
                        <label class="text-tiny font-black text-indigo-500 uppercase tracking-[0.2em] ml-2 mb-3 block">Patient (Child) Full Name</label>
                        <input type="text" wire:model="first_name" x-ref="firstName" placeholder="Enter Child's Full Name" 
                               class="w-full bg-transparent border-none p-0 text-2xl font-black text-gray-900 dark:text-white placeholder-gray-300 dark:placeholder-gray-700 outline-none transition-all shadow-none ring-0 focus:ring-0">
                        @error('first_name') <p class="text-tiny text-rose-500 font-black uppercase mt-2 ml-2">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Parents Info Group -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-form.input label="Mother Name" wire:model="mother_name" name="mother_name" placeholder="Mother's Full Name" />
                        @error('mother_name') <p class="text-tiny text-rose-500 font-black uppercase mt-1">{{ $message }}</p> @enderror
                    </div>
                    <x-form.input label="Father Name (Optional)" wire:model="father_name" name="father_name" placeholder="Father's Full Name" />
                </div>

                <!-- Physical Details & Contact -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <div>
                        <x-form.input label="Date of Birth" wire:model="date_of_birth" name="date_of_birth" type="date" />
                        @error('date_of_birth') <p class="text-tiny text-rose-500 font-black uppercase mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <x-form.select label="Gender" wire:model="gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </x-form.select>
                        @error('gender') <p class="text-tiny text-rose-500 font-black uppercase mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <x-form.input label="Mobile Phone" wire:model.live.debounce.300ms="phone" name="phone" placeholder="10 Digit Number" maxlength="10" onkeypress="return event.charCode >= 48 && event.charCode <= 57" />
                        @if($matchedPatientName)
                            <div class="mt-2 p-2 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-800/30 rounded-xl flex items-center gap-2 animate-in slide-in-from-top-1 duration-300">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <p class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">
                                    Recognized: {{ $matchedPatientName }}
                                </p>
                            </div>
                        @endif
                        @error('phone') <p class="text-tiny text-rose-500 font-black uppercase mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div x-data="{
                        open: false,
                        loading: false,
                        results: [],
                        controller: null,
                        async search(q) {
                            const query = (q || '').trim();
                            if (query.length < 2) {
                                this.results = [];
                                this.open = false;
                                return;
                            }

                            if (this.controller) this.controller.abort();
                            this.controller = new AbortController();

                            this.loading = true;
                            this.open = true;
                            try {
                                const url = new URL('{{ route('counter.address.autocomplete') }}', window.location.origin);
                                url.searchParams.set('q', query);
                                const res = await fetch(url.toString(), { signal: this.controller.signal, headers: { 'Accept': 'application/json' } });
                                if (!res.ok) throw new Error('Request failed');
                                this.results = await res.json();
                            } catch (e) {
                                if (e.name !== 'AbortError') {
                                    this.results = [];
                                }
                            } finally {
                                this.loading = false;
                            }
                        },
                        async select(item) {
                            if (item.source === 'google' && item.place_id) {
                                try {
                                    const url = new URL('{{ route('counter.address.details') }}', window.location.origin);
                                    url.searchParams.set('place_id', item.place_id);
                                    const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                                    if (res.ok) {
                                        const details = await res.json();
                                        if (details && details.address) {
                                            $wire.set('address', details.address || item.address);
                                            $wire.set('city', details.city || item.city);
                                            $wire.set('state', details.state || item.state);
                                            $wire.set('pincode', details.pincode || item.pincode);
                                            this.open = false;
                                            this.results = [];
                                            return;
                                        }
                                    }
                                } catch (e) {
                                    console.error('Failed to fetch address details', e);
                                }
                            }
                            
                            $wire.set('address', item.address || '');
                            $wire.set('city', item.city || '');
                            $wire.set('state', item.state || '');
                            $wire.set('pincode', item.pincode || '');
                            this.open = false;
                            this.results = [];
                        },
                    }" class="relative" @click.away="open = false" @keydown.escape.window="open = false">
                        <x-form.input
                            label="Address / Landmark"
                            wire:model="address"
                            name="address"
                            placeholder="Start typing street address or landmark..."
                            x-on:input.debounce.150ms="search($event.target.value)"
                            autocomplete="off"
                        />
                        @error('address') <p class="text-tiny text-rose-500 font-black uppercase mt-1">{{ $message }}</p> @enderror

                        <div x-show="open" x-transition class="absolute z-50 mt-2 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl overflow-hidden">
                            <div x-show="loading" class="px-4 py-3 text-sm text-slate-500">Searching...</div>
                            <template x-for="item in results" :key="item.place_id">
                                <button type="button" class="w-full text-left px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors" @click="select(item)">
                                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200" x-text="item.label"></div>
                                    <div class="text-xs text-slate-500 mt-1" x-text="item.subLabel"></div>
                                </button>
                            </template>
                            <div x-show="!loading && results.length === 0" class="px-4 py-3 text-sm text-slate-500">No results</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="md:col-span-2">
                            <x-form.input label="City / Town" wire:model="city" name="city" placeholder="City" />
                            @error('city') <p class="text-tiny text-rose-500 font-black uppercase mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <x-form.input label="State" wire:model="state" name="state" placeholder="State" />
                            @error('state') <p class="text-tiny text-rose-500 font-black uppercase mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <x-form.input label="Pincode" wire:model="pincode" name="pincode" placeholder="6 Digit Code" />
                            @error('pincode') <p class="text-tiny text-rose-500 font-black uppercase mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <x-form.input label="Blood Group (Optional)" wire:model="blood_group" name="blood_group" placeholder="A+, O+, ..." />
                        @error('blood_group') <p class="text-tiny text-rose-500 font-black uppercase mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>


            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 pt-8 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="$dispatch('close-modal', { name: 'patient-modal' })" 
                        class="px-8 py-4 text-tiny font-black uppercase tracking-[0.2em] text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Discard
                </button>
                <button type="submit" class="px-12 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[1.5rem] text-tiny font-black uppercase tracking-[0.2em] shadow-xl shadow-indigo-500/30 hover:scale-[1.02] active:scale-95 transition-all">
                    {{ $isEditing ? 'Update Profile' : 'Register & Proceed' }}
                </button>
            </div>
        </form>
    </x-modal>
</div>
