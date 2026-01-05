<!-- filepath: c:\laragon\www\Simlab\apps\web\resources\views\livewire\feature\course\cards\course-card-list.blade.php -->
@php
    use App\Enums\UserRoleEnum;
@endphp

<x-table.wrapper>
    {{-- Sticky Header (search + actions + bulk actions) --}}
    <x-table.header>
        <div class="p-4">
            <x-table.header-actions>
                {{-- Search Bar --}}
                <x-table.search-bar name="search" placeholder="Cari matakuliah berdasarkan nama..." />

                <div>

                    @if ($user->roles->contains('code', UserRoleEnum::LECTURER))
                    {{-- Actions --}}
                    <a href="/matakuliah/tambah" wire:navigate>
                        <div class="flex items-center gap-3">
                            <x-button leftIcon="add">Tambah Matakuliah</x-button>
                        </div>
                    </a>
                    @endif
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

    <x-cards.container>
        @forelse ($courses as $course)
            <x-cards.course-card :course="$course" wire:click="edit({{ $course->id }})">
                <x-slot:actions>
                    {{-- <a href="/course/{{ $course->id }}" class="btn btn-primary btn-sm">Detail</a> --}}
                    {{-- <button wire:click="$dispatch('showEditForm', { id: {{ $course->id }} }})" class="btn btn-outline btn-sm">Edit</button> --}}

                    @if ($canDelete)
                        <x-cards.action-menu
                            :id="$course->id"
                            :actions="[
                                [
                                    'action' => 'edit',
                                    'label' => 'Detail',
                                    'icon' => 'pencil-square',
                                    'class' => 'text-info'
                                ],
                                [
                                    'action' => 'delete',
                                    'label' => 'Hapus',
                                    'icon' => 'trash',
                                    'class' => 'text-error',
                                    'confirm' => 'Apakah Anda yakin ingin menghapus mahasiswa ini?'
                                ]
                            ]"
                        />
                    @else
                        <x-cards.action-menu
                            :id="$course->id"
                            :actions="[
                                [
                                    'action' => 'edit',
                                    'label' => 'Detail',
                                    'icon' => 'pencil-square',
                                    'class' => 'text-info'
                                ],
                            ]"
                        />
                    @endif
                </x-slot:actions>
            </x-cards.course-card>
        @empty
            @if ($user->roles->contains('code', UserRoleEnum::LECTURER))
                <x-cards.empty-state
                        :class="'col-span-3'"
                        title="Belum ada matakuliah"
                        actionLabel="Tambah Matakuliah Pertama"
                        actionEvent="$dispatch('showCreateForm')"
                />
            @else
                <x-cards.empty-state
                        :class="'col-span-3'"
                        title="Belum ada matakuliah"
                />
            @endif
        @endforelse
    </x-cards.container>

    {{ $courses->links() }}
</x-table.wrapper>
