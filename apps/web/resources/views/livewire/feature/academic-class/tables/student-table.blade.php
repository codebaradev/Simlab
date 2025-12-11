<x-table.wrapper>
    {{-- Sticky Header (search + actions + bulk actions) --}}
    <x-table.header>
        <div class="p-4">
            <x-table.header-actions>
                {{-- Search Bar --}}
                <x-table.search-bar name="search" placeholder="Cari mahasiswa berdasarkan NIM atau nama..." />

                <div>
                    <div class="flex items-center gap-3">
                        <x-button leftIcon="add" wire:click="$dispatch('showAddModal')">Tambah Mahasiswa</x-button>
                    </div>
                </div>

            </x-table.header-actions>
        </div>

        {{-- Bulk Actions --}}
        <x-table.bulk-actions
            :selected="$selected"
            itemName="mahasiswa"
            :actions="[
                [
                    'label' => 'Hapus',
                    'action' => 'deleteSelected',
                    'confirm' => 'Apakah Anda yakin ingin menghapus mahasiswa terpilih yang ada di kelas ini?',
                    'class' => 'btn-error',
                ],
            ]"
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
                {{-- <th class="w-32"></th> --}}
            </tr>
        </x-table.sticky-thead>

        <tbody>
            @forelse($students as $student)
                <tr wire:key="student-{{ $student->id }}"
                    class="group transition-all duration-200"
                    {{-- wire:click="editStudent({{ $student->id }})" --}}
                    @click.stop>
                    <x-table.checkbox-cell :value="$student->id" />
                    <td class="font-mono font-bold">{{ $student->nim }}</td>
                    <td>
                        <div class="flex flex-col">
                            <span class="font-semibold">{{ $student->user->name ?? $student->name }}</span>
                            <span class="text-xs text-gray-500">{{ $student->user->email ?? $student->email }}</span>
                        </div>
                    </td>
                    {{-- <x-table.action-menu
                        :id="$student->id"
                        :actions="[
                            [
                                'action' => 'deleteStudent',
                                'label' => 'Hapus',
                                'icon'   => 'trash',
                                'class' => 'text-error',
                                'confirm' => 'Apakah Anda yakin ingin menghapus mahasiswa ini?'
                            ]
                        ]"
                    /> --}}
                </tr>
            @empty
                <x-table.empty-state
                    colspan="7"
                    message="Tidak ada data mahasiswa"
                    actionLabel="Tambah Mahasiswa Pertama"
                    actionEvent="$dispatch('showFormModal')"
                />
            @endforelse
        </tbody>
    </x-table.container>

    {{-- Pagination --}}
    {{ $students->links() }}
</x-table.wrapper>
