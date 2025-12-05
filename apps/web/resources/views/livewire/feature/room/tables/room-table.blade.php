<x-table.wrapper>
    {{-- Sticky Header (search + actions + bulk actions) --}}
    <x-table.header>
        <div class="p-4">
            <x-table.header-actions>
                {{-- Search Bar --}}
                <x-table.search-bar name="search" placeholder="Cari ruangan berdasarkan NIM atau nama..." />

                {{-- Actions --}}
                <a href="/ruangan/tambah" wire:navigate>
                    <div class="flex items-center gap-3">
                        <x-button leftIcon="add" wire:click="$dispatch('showCreateForm')">Tambah Ruangan</x-button>
                    </div>
                </a>
            </x-table.header-actions>
        </div>

        {{-- Bulk Actions --}}
        <x-table.bulk-actions
            :selected="$selected"
            itemName="ruangan"
            deleteAction="deleteSelected"
            deleteConfirm="Apakah Anda yakin ingin menghapus ruangan terpilih?"
        />
    </x-table.header>

    {{-- Scrollable Table Area --}}
    <x-table.container>
        <x-table.sticky-thead>
            <tr>
                <x-table.checkbox-header />
                <x-table.sortable-header
                    field="code"
                    label="Kode Ruangan"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <x-table.sortable-header
                    field="name"
                    label="Nama Ruangan"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <x-table.unsortable-header
                    label="Status"
                />
                <th class="w-32"></th>
            </tr>
        </x-table.sticky-thead>

        <tbody>
            @forelse($rooms as $room)
                <tr wire:key="room-{{ $room->id }}"
                    class="group cursor-pointer transition-all duration-200 hover:bg-blue-50 {{ $room->status === 'nonaktif' ? 'bg-gray-50' : '' }}"
                    wire:click="editRoom({{ $room->id }})"
                    @click.stop>
                    <x-table.checkbox-cell :value="$room->id" />
                    <td class="font-mono font-bold">{{ $room->code }}</td>
                    <td>
                        <div class="flex flex-col">
                            <span class="font-semibold">{{ $room->user->name ?? $room->name }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="badge badge-{{$room->status->color()}}">{{ $room->status->label() }}</div>
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
                            $color = $statusColors[$room->status] ?? 'bg-gray-50 text-gray-700 ring-gray-600/20';
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $color }}">
                            {{ ucfirst($room->status) }}
                        </span>
                    </td> --}}
                    <x-table.action-menu
                        :id="$room->id"
                        :actions="[
                            [
                                'action' => 'editRoom',
                                'label' => 'Edit',
                                'icon' => 'pencil-square',
                                'class' => 'text-info'
                            ],
                            [
                                'action' => 'deleteRoom',
                                'label' => 'Hapus',
                                'icon' => 'trash',
                                'class' => 'text-error',
                                'confirm' => 'Apakah Anda yakin ingin menghapus ruangan ini?'
                            ]
                        ]"
                    />
                </tr>
            @empty
                <x-table.empty-state
                    colspan="7"
                    message="Tidak ada data ruangan"
                    actionLabel="Tambah Ruangan Pertama"
                    actionEvent="addRoom"
                />
            @endforelse
        </tbody>
    </x-table.container>

    {{-- Pagination --}}
    {{ $rooms->links() }}
</x-table.wrapper>
