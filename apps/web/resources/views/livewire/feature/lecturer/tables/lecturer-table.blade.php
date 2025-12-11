<x-table.wrapper>
    {{-- Sticky Header (search + actions + bulk actions) --}}
    <x-table.header>
        <div class="p-4">
            <x-table.header-actions>
                {{-- Search Bar --}}
                <x-table.search-bar name="search" placeholder="Cari dosen berdasarkan NIM atau nama..." />

                {{-- Actions --}}
                <a href="/dosen/tambah" wire:navigate>
                    <div class="flex items-center gap-3">
                        <x-button leftIcon="add" wire:click="$dispatch('showCreateForm')">Tambah Dosen</x-button>
                    </div>
                </a>
            </x-table.header-actions>

            {{-- Additional Filters (optional) --}}
            @if(isset($studyPrograms) && $studyPrograms->count())
                <div class="mt-4 flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">Filter:</span>
                        <x-form.select
                            name="study_program_id"
                            wire:model.live="selectedStudyProgram"
                            label="Program Studi"
                            :options="$studyPrograms"
                            optionValxue="id"
                            optionLabel="name"
                            placeholder="Semua Program Studi"
                            class="w-64"
                            inline
                            noLabel
                        />
                    </div>

                    @if(isset($academicYears) && $academicYears->count())
                        <x-form.select
                            name="generation"
                            wire:model.live="selectedAcademicYear"
                            label="Angkatan"
                            :options="$academicYears"
                            optionValue="id"
                            optionLabel="name"
                            placeholder="Semua Angkatan"
                            class="w-48"
                            inline
                            noLabel
                        />
                    @endif
                </div>
            @endif
        </div>

        {{-- Bulk Actions --}}
        <x-table.bulk-actions
            :selected="$selected"
            itemName="dosen"
            deleteAction="deleteSelected"
            deleteConfirm="Apakah Anda yakin ingin menghapus dosen terpilih?"
        />
    </x-table.header>

    {{-- Scrollable Table Area --}}
    <x-table.container>
        <x-table.sticky-thead>
            <tr>
                <x-table.checkbox-header />
                <x-table.sortable-header
                    field="name"
                    label="Nama Dosen"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <x-table.sortable-header
                    field="nidn"
                    label="NIDN"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <x-table.sortable-header
                    field="code"
                    label="Kode Dosen"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <x-table.sortable-header
                    field="study_program.name"
                    label="Program Studi"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <th class="w-32"></th>
            </tr>
        </x-table.sticky-thead>

        <tbody>
            @forelse($lecturers as $lecturer)
                <tr wire:key="lecturer-{{ $lecturer->id }}"
                    class="group cursor-pointer transition-all duration-200 hover:bg-blue-50 {{ $lecturer->status === 'nonaktif' ? 'bg-gray-50' : '' }}"
                    wire:click="editLecturer({{ $lecturer->id }})"
                    @click.stop>
                    <x-table.checkbox-cell :value="$lecturer->id" />
                    <td>
                        <div class="flex flex-col">
                            <span class="font-semibold">{{ $lecturer->user->name ?? $lecturer->name }}</span>
                            <span class="text-xs text-gray-500">{{ $lecturer->user->email ?? $lecturer->email }}</span>
                        </div>
                    </td>
                    <td class="font-mono font-bold">{{ $lecturer->nidn }}</td>
                    <td class="font-mono font-bold">{{ $lecturer->code }}</td>
                    <td>
                        <div class="flex flex-col">
                            <span>{{ $lecturer->study_program->name ?? '-' }}</span>
                            <span class="text-xs text-gray-500">{{ $lecturer->study_program->code ?? '' }}</span>
                        </div>
                    </td>
                    {{-- <td>
                        @php
                            $statusColors = [
                                'aktif' => 'bg-green-50 text-green-700 ring-green-600/20',
                                'nonaktif' => 'bg-red-50 text-red-700 ring-red-600/20',
                                'cuti' => 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
                                'lulus' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                'dropout' => 'bg-gray-50 text-gray-700 ring-gray-600/20',
                            ];
                            $color = $statusColors[$lecturer->status] ?? 'bg-gray-50 text-gray-700 ring-gray-600/20';
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $color }}">
                            {{ ucfirst($lecturer->status) }}
                        </span>
                    </td> --}}
                    <x-table.action-menu
                        :id="$lecturer->id"
                        :actions="[
                            [
                                'action' => 'editLecturer',
                                'label' => 'Edit',
                                'icon' => 'pencil-square',
                                'class' => 'text-info'
                            ],
                            [
                                'action' => 'deleteLecturer',
                                'label' => 'Hapus',
                                'icon' => 'trash',
                                'class' => 'text-error',
                                'confirm' => 'Apakah Anda yakin ingin menghapus dosen ini?'
                            ]
                        ]"
                    />
                </tr>
            @empty
                <x-table.empty-state
                    colspan="7"
                    message="Tidak ada data dosen"
                    actionLabel="Tambah dosen Pertama"
                    actionEvent="$dispatch('showCreateForm')"
                />
            @endforelse
        </tbody>
    </x-table.container>

    {{-- Pagination --}}
    {{ $lecturers->links() }}
</x-table.wrapper>
