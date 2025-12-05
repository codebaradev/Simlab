<x-table.wrapper>
    {{-- Sticky Header (search + actions + bulk actions) --}}
    <x-table.header>
        <div class="p-4">
            <x-table.header-actions>
                {{-- Search Bar --}}
                <x-table.search-bar name="search" placeholder="Cari komputer berdasarkan nama..." />

                {{-- Actions --}}
                <a href="/ruangan/{{ $room->id }}/komputer/tambah" wire:navigate>
                    <div class="flex items-center gap-3">
                        <x-button leftIcon="add" wire:click="$dispatch('showCreateForm')">Tambah Komputer</x-button>
                    </div>
                </a>
            </x-table.header-actions>
        </div>

        {{-- Bulk Actions --}}
        <x-table.bulk-actions
            :selected="$selected"
            itemName="komputer"
            deleteAction="deleteSelected"
            deleteConfirm="Apakah Anda yakin ingin menghapus komputer terpilih?"
        />
    </x-table.header>

    {{-- Scrollable Table Area --}}
    <x-table.container>
        <x-table.sticky-thead>
            <tr>
                <x-table.checkbox-header />
                <x-table.sortable-header
                    field="name"
                    label="Nama Komputer"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <x-table.unsortable-header
                    label="Spesifikasi"
                />
                <x-table.sortable-header
                    field="computer_count"
                    label="Jumlah Komputer"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <th class="w-32"></th>
            </tr>
        </x-table.sticky-thead>

        <tbody>
            @forelse($computers as $computer)
                <tr wire:key="room-{{ $computer->id }}"
                    class="group cursor-pointer transition-all duration-200 hover:bg-blue-50 {{ $computer->status === 'nonaktif' ? 'bg-gray-50' : '' }}"
                    wire:click="editComputer({{ $computer->id }})"
                    @click.stop>
                    <x-table.checkbox-cell :value="$computer->id" />
                    <td class="font-mono font-bold">{{ $computer->name }}</td>
                    <td>
                        <div class="badge badge-soft badge-{{ $computer->category->color() }}">{{ $computer->category->label() }}</div>
                    </td>
                    <td>{{ $computer->computer_count }}</td>
                    <x-table.action-menu
                        :id="$computer->id"
                        :actions="[
                            [
                                'action' => 'editComputer',
                                'label' => 'Edit',
                                'icon' => 'pencil-square',
                                'class' => 'text-info'
                            ],
                            [
                                'action' => 'deleteComputer',
                                'label' => 'Hapus',
                                'icon' => 'trash',
                                'class' => 'text-error',
                                'confirm' => 'Apakah Anda yakin ingin menghapus komputer ini?'
                            ]
                        ]"
                    />
                </tr>
            @empty
                <x-table.empty-state
                    colspan="7"
                    message="Tidak ada data komputer"
                    actionLabel="Tambah Komputer Pertama"
                    actionEvent="addComputer"
                />
            @endforelse
        </tbody>
    </x-table.container>

    {{-- Pagination --}}
    {{ $computers->links() }}
</x-table.wrapper>
