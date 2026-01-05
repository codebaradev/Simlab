<x-table.wrapper>
    {{-- Sticky Header (search + actions + bulk actions) --}}
    <x-table.header>
        <div class="p-4">
            <x-table.header-actions>
                {{-- Search Bar --}}
                <x-table.search-bar name="search" placeholder="Cari Kelas berdasarkan nama dan kode..." />

                {{-- Actions --}}
                <a href="/prodi/{{ $studyProgram->id }}/kelas/tambah" wire:navigate>
                    <div class="flex items-center gap-3">
                        <x-button leftIcon="add" wire:click="$dispatch('showCreateForm')">Tambah Kelas</x-button>
                    </div>
                </a>
            </x-table.header-actions>
        </div>

        {{-- Bulk Actions --}}
        <x-table.bulk-actions
            :selected="$selected"
            itemName="kelas"
            deleteAction="deleteSelected"
            deleteConfirm="Apakah Anda yakin ingin menghapus kelas terpilih?"
        />
    </x-table.header>

    {{-- Scrollable Table Area --}}
    <x-table.container>
        <x-table.sticky-thead>
            <tr>
                <x-table.checkbox-header />
                <x-table.sortable-header
                    field="name"
                    label="Nama Kelas"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <x-table.unsortable-header
                    label="Jenis"
                />
                <x-table.sortable-header
                    field="generation"
                    label="Angkatan"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <x-table.sortable-header
                    field="year_semester"
                    label="Tahun Ajaran"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <th class="w-32"></th>
            </tr>
        </x-table.sticky-thead>

        <tbody>
            @forelse($academicClasses as $ac)
                <tr wire:key="room-{{ $ac->id }}"
                    class="group cursor-pointer transition-all duration-200 hover:bg-blue-50 {{ $ac->status === 'nonaktif' ? 'bg-gray-50' : '' }}"
                    wire:click="edit({{ $ac->id }})"
                    @click.stop>
                    <x-table.checkbox-cell :value="$ac->id" />
                    <td class="font-mono font-bold">
                        <div class="flex flex-col">
                            <span class="font-semibold">{{ $ac->name }}</span>
                            <span class="text-xs text-gray-500">{{ $ac->code  }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="badge badge-soft badge-{{ $ac->type->color() }}">{{ $ac->type->label() }}</div>
                    </td>
                    <td>{{ $ac->generation }}</td>
                    <td>
                        <div class="flex flex-col">
                            <span class="font-semibold">{{ $ac->year }}</span>
                            <span class="text-xs text-gray-500">{{ $ac->semester->label()  }}</span>
                        </div>
                    </td>

                    <x-table.action-menu
                        :id="$ac->id"
                        :actions="[
                            [
                                'action' => 'edit',
                                'label' => 'Edit',
                                'icon' => 'pencil-square',
                                'class' => 'text-info'
                            ],
                            [
                                'action' => 'delete',
                                'label' => 'Hapus',
                                'icon' => 'trash',
                                'class' => 'text-error',
                                'confirm' => 'Apakah Anda yakin ingin menghapus Kelas ini?'
                            ]
                        ]"
                    />
                </tr>
            @empty
                <x-table.empty-state
                    colspan="7"
                    message="Tidak ada data Kelas"
                    actionLabel="Tambah Kelas Pertama"
                    actionEvent="add"
                />
            @endforelse
        </tbody>
    </x-table.container>

    {{-- Pagination --}}
    {{ $academicClasses->links() }}
</x-table.wrapper>
