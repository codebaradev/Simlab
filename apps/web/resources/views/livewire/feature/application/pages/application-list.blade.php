<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Ruangan"
        :breadcrumbs="[
            ['label' => 'Ruangan', 'url' => '/ruangan'],
            ['label' => $room->code, 'url' => '/ruangan/'. $room->id],
            ['label' => 'Aplikasi'],
        ]"
    />

    <!-- Content -->

    <div class="tabs tabs-lift">
        <a href="/ruangan/{{ $room->id  }}" class="tab cursor-pointer" wire:navigate>Data Ruangan</a>

        <a href="/ruangan/{{ $room->id  }}/komputer" class="tab cursor-pointer" wire:navigate>Komputer</a>
        <a href="/ruangan/{{ $room->id  }}/aplikasi" class="tab cursor-pointer tab-active" wire:navigate>Aplikasi</a>
        <div class="tab-content">
            <livewire:feature.application.tables.application-table
                :room="$room"
            />
        </div>
    </div>

    <!-- Form Modal -->
    <dialog id="app_form_modal" class="modal" @if($showFormModal) open @endif>
        <div class="modal-box">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" wire:click="closeFormModal">✕</button>
            </form>

            <h3 class="font-bold text-lg mb-4">
                {{ $editingAppId ? 'Edit Aplikasi' : 'Tambah Aplikasi' }}
            </h3>

            <livewire:feature.application.forms.application-form
                :room="$room"
                :editingId="$editingAppId"
                :formData="$formData"
            />
        </div>

        <form method="dialog" class="modal-backdrop">
            <button wire:click="closeFormModal">close</button>
        </form>
    </dialog>
</div>

<script>
    // Handle modal state with Alpine.js
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('showFormModal', () => {
            document.getElementById('study_program_form_modal').showModal();
        });

        Livewire.on('closeFormModal', () => {
            document.getElementById('study_program_form_modal').close();
        });
    });
</script>

