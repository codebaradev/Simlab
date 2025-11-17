<div>
    <!-- Table Header with Actions -->
    <div class="p-4 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- Search Bar -->
            <div class="flex-1 max-w-md">
                <x-form.input name="search" rightIcon="search" class="max-w-xs" :live="true"/>
            </div>

            <!-- Right Side Actions -->
            <div class="flex items-center gap-3">
                <!-- Tambah Button -->
                <x-button leftIcon="add" wire:click="$dispatch('showCreateForm')">Tambah Jurusan</x-button>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if(count($selected) > 0)
        <div class="p-4 border-b border-gray-200 bg-warning/10">
            <div class="flex items-center gap-3">
                <span class="text-sm text-warning">
                    {{ count($selected) }} jurusan dipilih
                </span>
                <button type="button" class="btn btn-error btn-sm" wire:click="deleteSelected" wire:confirm="Apakah Anda yakin ingin menghapus jurusan terpilih?">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Hapus
                </button>
                <button type="button" wire:click="$set('selected', [])" class="btn btn-ghost btn-sm">
                    Batal
                </button>
            </div>
        </div>
    @endif

    <!-- Table -->
    <div class="overflow-x-auto overflow-y-visible">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-12">
                        <label>
                            <input type="checkbox" class="checkbox checkbox-sm" wire:model.live="selectAll" />
                        </label>
                    </th>
                    <th class="cursor-pointer" wire:click="sortBy('code')">
                        <div class="flex items-center gap-1">
                            <span>Kode</span>
                            @if($sortField === 'code')
                                <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th class="cursor-pointer" wire:click="sortBy('name')">
                        <div class="flex items-center gap-1">
                            <span>Nama Jurusan</span>
                            @if($sortField === 'name')
                                <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th class="w-20"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $department)
                    <tr wire:key="department-{{ $department->id }}">
                        <td>
                            <label>
                                <input type="checkbox" class="checkbox checkbox-sm" wire:model.live="selected" value="{{ $department->id }}" />
                            </label>
                        </td>
                        <td class="font-mono font-bold text-primary">{{ $department->code }}</td>
                        <td class="font-semibold">{{ $department->name }}</td>
                        <td>
                            <div class="dropdown dropdown-left">
                                <label tabindex="0" class="btn btn-ghost btn-xs">
                                    <x-icon.ellipsis class="size-6"/>
                                </label>
                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                                    <li>
                                        <x-button type="button" variant="edit-action">Edit</x-button>
                                        <button type="button" wire:click="editDepartment({{ $department->id }})" class="text-info">
                                            <x-icon.pencil-square class="size-4"/>
                                            Edit
                                        </button>
                                    </li>
                                    <li>
                                        <button type="button" wire:click="deleteDepartment({{ $department->id }})" wire:confirm="Apakah Anda yakin ingin menghapus jurusan ini?" class="text-error">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Hapus
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-500">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <span>Tidak ada data jurusan</span>
                                <button class="btn btn-primary btn-sm mt-2" wire:click="$dispatch('showCreateForm')">
                                    Tambah Jurusan Pertama
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($departments->hasPages())
            {{ $departments->links() }}
    @endif
</div>
