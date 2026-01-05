<div>
    <form>
        <div>
            <x-table.header>
                <div class="p-4">
                    <x-table.header-actions>
                        {{-- Search Bar --}}
                        <x-table.search-bar inputClass="w-full" name="search" placeholder="Cari mahasiswa berdasarkan NIM atau nama..." />
                    </x-table.header-actions>
                </div>

                {{-- Bulk Actions --}}
                <x-table.bulk-actions
                    :selected="$selected"
                    itemName="mahasiswa"
                    :actions="[
                        [
                            'label' => 'Tambah',
                            'action' => 'addSelected',
                            'confirm' => 'Apakah Anda yakin ingin menambahkan mahasiswa terpilih kedalam kelas ini?',
                            'class' => 'btn-info',
                        ],
                    ]"
                />

            </x-table.header>

            <x-table.container>
                <x-table.thead>
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
                            field="generation"
                            label="Angkatan"
                            :sortField="$sortField"
                            :sortDirection="$sortDirection"
                        />
                    </tr>
                </x-table.thead>

                <tbody>
                    @forelse($students as $student)
                        <tr wire:key="student-{{ $student->id }}"
                            class="group cursor-pointer transition-all duration-200 hover:bg-blue-50 {{ $student->status === 'nonaktif' ? 'bg-gray-50' : '' }}"
                            {{-- wire:click="toggleSelect({{ $student->id }})" --}}
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
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                    {{ $student->generation ?? '-' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <x-table.empty-state
                            colspan="7"
                            message="Tidak ada data mahasiswa"
                            {{-- actionLabel="Tambah Mahasiswa Pertama" --}}
                            {{-- actionEvent="$dispatch('showCreateForm')" --}}
                        />
                    @endforelse
                </tbody>
            </x-table.container>

            {{ $students->links() }}
        </div>
    </form>
</div>
