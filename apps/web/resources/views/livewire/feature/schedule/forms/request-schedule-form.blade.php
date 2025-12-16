<div>
    <div class="mb-3">
        <div class="tabs tabs-box ">
            <a class="tab w-1/2 font-bold {{ $activeTab === 'matakuliah' ? 'tab-active bg-primary text-white' : 'text-black' }}" wire:click="$set('activeTab','matakuliah')">Matakuliah</a>
            <a class="tab w-1/2 font-bold {{ $activeTab === 'lainnya' ? 'tab-active bg-primary text-white' : 'text-black' }}" wire:click="$set('activeTab','lainnya')">Lainnya</a>
        </div>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 card bg-base-100 border border-base-300 rounded-lg p-4">
            <!-- Left: form (2 cols on lg) -->
            <div class="">
                <div class="">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if ($activeTab === 'matakuliah')
                            <x-form.search-select class="col-span-2" name="course_id" label="Mata Kuliah" :options="$courses" optionValue="id" optionLabel="name" placeholder="Cari mata kuliah..." :required="true"/>
                        @endif
                        <x-form.search-select class="col-span-2" name="lecturer_id" label="Dosen Pengampu / Terlibat" :options="$lecturers" optionValue="id" optionLabel="user_full_name" placeholder="Cari dosen..." :required="true"/>

                        @if ($activeTab === 'lainnya')
                            <x-form.select class="col-span-2" name="category" label="Keperluan (Lainnya)" :options="$options['categories'] ?? []" optionValue="value" optionLabel="label" placeholder="Pilih Jenis" :required="true"/>
                        @endif

                        <x-form.input class="col-span-2" name="repeat_count" label="Repeat (jumlah pertemuan)" :required="true" :live="true"/>

                        <x-form.input type="date" class="col-span-2" name="start_date" label="Tanggal" :required="true" :live="true"/>
                        <x-form.select class="col-span-2" name="time" label="Waktu" :required="true" :live="true" :options="$options['times']" optionValue="value" optionLabel="label"/>

                        <x-form.textarea class="col-span-2" name="information" label="Informasi Tambahan" rows="3"  />
                    </div>
                </div>
            </div>

            <!-- Right: occurrences list (sidebar) -->
            <div class="col-span-2">
                <div class="col-span-2">
                    <label class="label mb-2"><span class="label-text font-medium">Ruang (pilih satu atau lebih)</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach($rooms as $r)
                            <label class="cursor-pointer border rounded-lg p-3 hover:shadow transition flex flex-col gap-2" for="room-{{ $r->id }}">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="font-semibold text-sm">{{ $r->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $r->code ?? '' }}</div>
                                    </div>
                                    <input id="room-{{ $r->id }}" type="checkbox" wire:model.live.debounce.300ms="room_ids" value="{{ $r->id }}" class="checkbox checkbox-primary" />
                                </div>
                                @if(!empty($r->capacity))
                                    <div class="text-xs text-gray-500">Kapasitas: {{ $r->capacity }}</div>
                                @endif
                                @if(!empty($r->location))
                                    <div class="text-xs text-gray-500">Lokasi: {{ $r->location }}</div>
                                @endif
                            </label>
                        @endforeach
                    </div>
                    @error('room_ids') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold">Daftar Jadwal</h3>
                        <div class="text-sm text-gray-500">{{ count($occurrences) }} item</div>
                    </div>

                    <div class="space-y-3 overflow-y-auto overflow-x-auto max-h-[80vh] whitespace-nowrap">
                        @forelse($occurrences as $i => $occ)
                            <div class="border rounded-lg p-3 bg-base-50 w-full">
                                <div class="flex items-center justify-between gap-2 ">
                                    <div class="flex-1 flex gap-2 items-center">
                                        <x-form.input type="date" name="occurrences.{{ $i }}.start_date"/>

                                        <x-form.select type="time" name="occurrences.{{ $i }}.time" placeholder="Pilih Sesi" :options="$options['times']" optionValue="value" optionLabel="label"/>
                                        {{-- <x-form.select class="min-w-32" name="occurrences.{{ $i }}.room_id" :options="$rooms" placeholder="Pilih Ruangan"/> --}}
                                    </div>

                                    <div class="flex flex-col items-center ml-2">
                                        <button type="button" class="btn btn-ghost btn-sm" title="Hapus" wire:click="removeOccurrence({{ $i }})">✕</button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">Belum ada jadwal. Atur tanggal / ruang lalu klik "Generate" atau tambah manual.</div>
                        @endforelse
                    </div>

                    {{-- <div class="mt-4 flex gap-2">
                        <button type="button" class="btn btn-outline btn-sm flex-1" wire:click="generateOccurrences">Generate</button>
                        <button type="button" class="btn btn-primary btn-sm flex-1" wire:click="addOccurrence">Tambah</button>
                    </div> --}}
                </div>
            </div>

            {{-- Action --}}
            <div class="col-span-3 flex justify-end gap-2">
                <x-button type="button" variant="outline" wire:click="$dispatch('closeRequestFormModal')">Batal</x-button>
                <x-button type="submit" leftIcon="add" variant="primary">Request</x-button>
            </div>
        </div>
    </form>
</div>
