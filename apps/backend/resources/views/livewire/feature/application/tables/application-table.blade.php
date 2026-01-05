<x-table.wrapper>
    {{-- Sticky Header (search + actions + bulk actions) --}}
    <x-table.header>
        <div class="p-4">
            <x-table.header-actions>
                {{-- Search Bar --}}
                <x-table.search-bar name="search" placeholder="Cari aplikasi..." />


                <div class="flex gap-2">
                    {{-- Actions --}}
                    <div class="flex items-center gap-3">
                        <x-button leftIcon="add" wire:click="$dispatch('showCreateForm')">Tambah Aplikasi</x-button>
                    </div>
                </div>
            </x-table.header-actions>
        </div>

        {{-- Bulk Actions --}}
        <x-table.bulk-actions
            :selected="$selected"
            itemName="aplikasi"
            deleteAction="deleteSelected"
            deleteConfirm="Apakah Anda yakin ingin menghapus aplikasi terpilih?"
        />
    </x-table.header>

    {{-- Scrollable Table Area --}}
    <x-table.container>
        <x-table.sticky-thead>
            <tr>
                <x-table.checkbox-header />
                <x-table.sortable-header
                    field="name"
                    label="Nama"
                    :sortField="$sortField"
                    :sortDirection="$sortDirection"
                />
                <th class="w-20"></th>
            </tr>
        </x-table.sticky-thead>

        <tbody>
            @forelse($apps as $app)
                <tr wire:key="app-{{ $app->id }}"
                    class="group cursor-pointer transition-all duration-200 hover:bg-blue-50"
                    wire:click="editApp({{ $app->id }})"
                    @click.stop>
                    <x-table.checkbox-cell :value="$app->id" />
                    <td class="font-mono font-bold text-primary">{{ $app->name }}</td>
                    <x-table.action-menu
                        :id="$app->id"
                        :actions="[
                            [
                                'action' => 'editApp',
                                'label' => 'Edit',
                                'icon' => 'pencil-square',
                                'class' => 'text-info'
                            ],
                            [
                                'action' => 'deleteApp',
                                'label' => 'Hapus',
                                'icon' => 'trash',
                                'class' => 'text-error',
                                'confirm' => 'Apakah Anda yakin ingin menghapus aplikasi ini?'
                            ]
                        ]"
                    />
                </tr>
            @empty
                <x-table.empty-state
                    colspan="4"
                    message="Tidak ada data aplikasi"
                    actionLabel="Tambah aplikasi Pertama"
                    actionEvent="$dispatch('showCreateForm')"
                />
            @endforelse
        </tbody>
    </x-table.container>

    {{-- Pagination --}}
    {{ $apps->links() }}
</x-table.wrapper>
