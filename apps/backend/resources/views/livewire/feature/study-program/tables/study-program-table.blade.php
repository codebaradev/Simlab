<x-table.wrapper>
    {{-- Sticky Header (search + actions + bulk actions) --}}
    <x-table.header>
        <div class="p-4">
            <x-table.header-actions>
                {{-- Search Bar --}}
                <x-table.search-bar name="search" placeholder="Cari program studi..." />


                <div class="flex gap-2">
                    <x-table.filter-dropdown
                        :filters="[
                            [
                                'name' => 'selectedDepartment',
                                'label' => 'Jurusan',
                                'options' => $departments,
                                'optionValue' => 'id',
                                'optionLabel' => 'name',
                                'placeholder' => 'Semua Jurusan'
                            ]
                        ]"
                        :activeCount="$activeFilterCount"
                        label="Filter"
                        icon="funnel"
                    />

                    {{-- Actions --}}

                    <a href="/prodi/tambah">
                        <div class="flex items-center gap-3">
                            <x-button leftIcon="add" wire:click="$dispatch('showCreateForm')">Tambah Program Studi</x-button>
                        </div>
                    </a>
                </div>
            </x-table.header-actions>
        </div>

        {{-- Bulk Actions --}}
        <x-table.bulk-actions
            :selected="$selected"
            itemName="program studi"
            deleteAction="deleteSelected"
        />
    </x-table.header>

    {{-- Scrollable Table Area --}}
    <x-table.container>
        <x-table.sticky-thead>
            <tr>
                <x-table.checkbox-header />
                <x-table.sortable-header
                    field="code"
                    label="Kode"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <x-table.sortable-header
                    field="name"
                    label="Program Studi"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <x-table.unsortable-header
                    label="Jurusan"
                />
                <th class="w-20"></th>
            </tr>
        </x-table.sticky-thead>

        <tbody>
            @forelse($studyPrograms as $studyProgram)
                <tr wire:key="study-program-{{ $studyProgram->id }}"
                    class="group cursor-pointer transition-all duration-200 hover:bg-blue-50"
                    wire:click="editStudyProgram({{ $studyProgram->id }})"
                    @click.stop>
                    <x-table.checkbox-cell :value="$studyProgram->id" />
                    <td class="font-mono font-bold text-primary">{{ $studyProgram->code }}</td>
                    <td class="font-semibold">{{ $studyProgram->name }}</td>
                    <td class="font-semibold">{{ $studyProgram->department->name }}</td>
                    <x-table.action-menu
                        :id="$studyProgram->id"
                        :actions="[
                            [
                                'action' => 'editStudyProgram',
                                'label' => 'Edit',
                                'icon' => 'pencil-square',
                                'class' => 'text-info'
                            ],
                            [
                                'action' => 'deleteStudyProgram',
                                'label' => 'Hapus',
                                'icon' => 'trash',
                                'class' => 'text-error',
                                'confirm' => 'Apakah Anda yakin ingin menghapus program studi ini?'
                            ]
                        ]"
                    />
                </tr>
            @empty
                <x-table.empty-state
                    colspan="4"
                    message="Tidak ada data program studi"
                    actionLabel="Tambah Program Studi Pertama"
                    actionEvent="$dispatch('showCreateForm')"
                />
            @endforelse
        </tbody>
    </x-table.container>

    {{-- Pagination --}}
    {{ $studyPrograms->links() }}
</x-table.wrapper>
