<x-table.wrapper>
    {{-- Sticky Header (search + actions + bulk actions) --}}
    <x-table.header>
        <div class="p-4">
            <x-table.header-actions>
                {{-- Search Bar --}}
                <x-table.search-bar name="search" placeholder="Cari mahasiswa berdasarkan NIM atau nama..." />

                <div>
                    {{-- <x-table.filter-dropdown
                        :filters="[
                            [
                                'name' => 'selectedDepartment',
                                'label' => 'Jurusan',
                                'options' => $studyPrograms,
                                'optionValue' => 'id',
                                'optionLabel' => 'name',
                                'placeholder' => 'Semua Prodi'
                            ]
                        ]"
                        :activeCount="$activeFilterCount"
                        label="Filter"
                        icon="funnel"
                    /> --}}

                    {{-- Actions --}}
                    <a href="/mahasiswa/tambah" wire:navigate>
                        <div class="flex items-center gap-3">
                            <x-button leftIcon="add" wire:click="$dispatch('showCreateForm')">Tambah Mahasiswa</x-button>
                        </div>
                    </a>
                </div>

            </x-table.header-actions>
        </div>

        {{-- Bulk Actions --}}
        <x-table.bulk-actions
            :selected="$selected"
            itemName="mahasiswa"
            deleteAction="deleteSelected"
            deleteConfirm="Apakah Anda yakin ingin menghapus mahasiswa terpilih?"
        />
    </x-table.header>

    {{-- Scrollable Table Area --}}
    <x-table.container>
        <x-table.sticky-thead>
            <tr>
                <x-table.checkbox-header />
                <x-table.sortable-header
                    field="nim"
                    label="NIM"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <x-table.sortable-header
                    field="name"
                    label="Nama Mahasiswa"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <x-table.sortable-header
                    field="study_program.name"
                    label="Program Studi"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <x-table.sortable-header
                    field="generation"
                    label="Angkatan"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <x-table.unsortable-header
                    label="Kelas"
                />
                <th class="w-32"></th>
            </tr>
        </x-table.sticky-thead>

        <tbody>
            @forelse($students as $student)
                <tr wire:key="student-{{ $student->id }}"
                    class="group cursor-pointer transition-all duration-200 hover:bg-blue-50 {{ $student->status === 'nonaktif' ? 'bg-gray-50' : '' }}"
                    wire:click="editStudent({{ $student->id }})"
                    @click.stop>
                    <x-table.checkbox-cell :value="$student->id" />
                    <td class="font-mono font-bold">{{ $student->nim }}</td>
                    <td>
                        <div class="flex flex-col">
                            <span class="font-semibold">{{ $student->user->name ?? $student->name }}</span>
                            <span class="text-xs text-gray-500">{{ $student->user->email ?? $student->email }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="flex flex-col">
                            <span>{{ $student->study_program->name ?? '-' }}</span>
                            <span class="text-xs text-gray-500">{{ $student->study_program->code ?? '' }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">
                            {{ $student->generation ?? '-' }}
                        </span>
                    </td>
                    <td>
                        {{ $student->first_class->code ?? '-' }}
                    </td>
                    <x-table.action-menu
                        :id="$student->id"
                        :actions="[
                            [
                                'action' => 'editStudent',
                                'label' => 'Edit',
                                'icon' => 'pencil-square',
                                'class' => 'text-info'
                            ],
                            [
                                'action' => 'deleteStudent',
                                'label' => 'Hapus',
                                'icon' => 'trash',
                                'class' => 'text-error',
                                'confirm' => 'Apakah Anda yakin ingin menghapus mahasiswa ini?'
                            ]
                        ]"
                    />
                </tr>
            @empty
                <x-table.empty-state
                    colspan="7"
                    message="Tidak ada data mahasiswa"
                    actionLabel="Tambah Mahasiswa Pertama"
                    actionEvent="$dispatch('showCreateForm')"
                />
            @endforelse
        </tbody>
    </x-table.container>

    {{-- Pagination --}}
    {{ $students->links() }}
</x-table.wrapper>
