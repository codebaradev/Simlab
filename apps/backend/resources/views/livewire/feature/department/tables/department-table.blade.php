<x-table.wrapper>
    {{-- Sticky Header (search + actions + bulk actions) --}}
    <x-table.header>
        <div class="p-4">
            <x-table.header-actions>
                {{-- Search Bar --}}
                <x-table.search-bar name="search" placeholder="Cari jurusan..." />

                {{-- Actions --}}
                <div class="flex items-center gap-3">
                    <x-button leftIcon="add" wire:click="$dispatch('showCreateForm')">Tambah Jurusan</x-button>
                </div>
            </x-table.header-actions>
        </div>

        {{-- Bulk Actions --}}
        <x-table.bulk-actions
            :selected="$selected"
            itemName="jurusan"
            deleteAction="deleteSelected"
            deleteConfirm="Apakah Anda yakin ingin menghapus jurusan terpilih?"
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
                    label="Nama Jurusan"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <th class="w-20"></th>
            </tr>
        </x-table.sticky-thead>

        <tbody>
            @forelse($departments as $department)
                <tr wire:key="department-{{ $department->id }}"
                    class="group cursor-pointer transition-all duration-200 hover:bg-blue-50"
                    wire:click="editDepartment({{ $department->id }})"
                    @click.stop>
                    <x-table.checkbox-cell :value="$department->id" />
                    <td class="font-mono font-bold text-primary">{{ $department->code }}</td>
                    <td class="font-semibold">{{ $department->name }}</td>
                    <x-table.action-menu
                        :id="$department->id"
                        :actions="[
                            [
                                'action' => 'editDepartment',
                                'label' => 'Edit',
                                'icon' => 'pencil-square',
                                'class' => 'text-info'
                            ],
                            [
                                'action' => 'deleteDepartment',
                                'label' => 'Hapus',
                                'icon' => 'trash',
                                'class' => 'text-error',
                                'confirm' => 'Apakah Anda yakin ingin menghapus jurusan ini?'
                            ]
                        ]"
                    />
                </tr>
            @empty
                <x-table.empty-state
                    colspan="4"
                    message="Tidak ada data jurusan"
                    actionLabel="Tambah Jurusan Pertama"
                    actionEvent="$dispatch('showCreateForm')"
                />
            @endforelse
        </tbody>
    </x-table.container>

    {{-- Pagination --}}
    {{ $departments->links() }}
</x-table.wrapper>
