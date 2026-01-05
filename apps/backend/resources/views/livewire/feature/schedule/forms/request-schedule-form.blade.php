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
                            <x-form.search-select class="col-span-2" name="course_id" label="Mata Kuliah" :options="$courses" optionValue="id" optionLabel="name" placeholder="Cari mata kuliah..." :required="true" :live="true"/>
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
                    {{-- <label class="label mb-2"><span class="label-text font-medium">Ruang (pilih satu atau lebih)</span></label> --}}
                    {{-- <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
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
                    </div> --}}
                    <div class="col-span-2">
                            <div class="flex justify-between items-end mb-2">
                                <label class="label p-0"><span class="label-text font-medium">Ruang (pilih satu atau lebih)</span></label>

                                <div wire:loading wire:target="updatedCourseId" class="text-xs text-info flex items-center gap-1 animate-pulse">
                                    <span class="loading loading-spinner loading-xs"></span> AI sedang menghitung...
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($rooms as $r)
                                    @php
                                        // Cek apakah ruangan ini direkomendasikan AI
                                        // Kita cari data AI berdasarkan room_id
                                        $aiData = collect($recommendations)->firstWhere('room_id', (string)$r->id);
                                        $isRecommended = !empty($aiData);
                                    @endphp

                                    <label class="cursor-pointer border rounded-lg p-3 transition flex flex-col gap-2 relative group h-full
                                        {{-- Style Dinamis: Jika direkomendasikan, border biru & bg biru muda --}}
                                        {{ $isRecommended ? 'border-blue-500 bg-blue-50 shadow-md ring-1 ring-blue-400' : 'border-base-300 hover:border-primary hover:shadow' }}
                                        {{-- Style Dinamis: Jika dicentang manual, tebalkan border --}}
                                        {{ in_array($r->id, $room_ids) ? 'ring-2 ring-primary border-primary' : '' }}"
                                        for="room-{{ $r->id }}">

                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex-1">
                                                <div class="font-semibold text-sm flex flex-wrap items-center gap-1">
                                                    {{ $r->name }}

                                                    {{-- Badge Skor AI --}}
                                                    @if($isRecommended)
                                                        <span class="badge badge-xs badge-primary font-bold text-[10px] h-5 px-2">
                                                            {{ number_format($aiData['score'] * 100, 0) }}% ✨
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-500">{{ $r->code ?? '' }}</div>
                                            </div>

                                            <input id="room-{{ $r->id }}" type="checkbox" wire:model.live.debounce.300ms="room_ids" value="{{ $r->id }}" class="checkbox checkbox-primary checkbox-sm" />
                                        </div>

                                        {{-- Info Dasar --}}
                                        <div class="flex flex-wrap gap-2 text-[11px] text-gray-500 mt-1">
                                            @if(!empty($r->capacity))
                                                <span class="bg-base-200 px-1.5 py-0.5 rounded">Kap: {{ $r->capacity }}</span>
                                            @endif
                                            @if(!empty($r->location))
                                                <span class="bg-base-200 px-1.5 py-0.5 rounded">Lok: {{ $r->location }}</span>
                                            @endif
                                        </div>

                                        {{-- DETAIL REKOMENDASI AI (Hanya muncul jika recommended) --}}
                                        @if($isRecommended)
                                            <div class="mt-2 pt-2 border-t border-blue-200 text-[10px] text-gray-700 grid grid-cols-2 gap-y-1">
                                                <div class="flex items-center gap-1" title="Kecukupan RAM">
                                                    <span class="{{ $aiData['details']['status_ram'] == 'Cukup' ? 'text-green-600 font-bold' : 'text-red-500 font-bold' }}">
                                                        {{ $aiData['details']['status_ram'] == 'Cukup' ? '✔' : '✖' }}
                                                    </span>
                                                    RAM: {{ $aiData['details']['status_ram'] }}
                                                </div>

                                                <div class="flex items-center gap-1" title="Ketersediaan GPU">
                                                    <span class="{{ in_array($aiData['details']['status_gpu'], ['Ada', 'Cocok']) ? 'text-green-600 font-bold' : 'text-orange-500 font-bold' }}">
                                                        {{ in_array($aiData['details']['status_gpu'], ['Ada', 'Cocok']) ? '✔' : '⚠' }}
                                                    </span>
                                                    GPU: {{ $aiData['details']['status_gpu'] }}
                                                </div>

                                                <div class="col-span-2 mt-1">
                                                    <div class="flex justify-between mb-0.5">
                                                        <span>Software:</span>
                                                        <span class="font-bold">{{ $aiData['details']['match_software'] }}</span>
                                                    </div>
                                                    <div class="w-full bg-blue-200 rounded-full h-1.5">
                                                        <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-500" style="width: {{ $aiData['details']['match_software'] }}"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                            @error('room_ids') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
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
