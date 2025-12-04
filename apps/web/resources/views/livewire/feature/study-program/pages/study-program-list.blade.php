<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Program Studi"
        :breadcrumbs="[
            ['label' => 'Program Studi'],
        ]"
    />

    <!-- Content -->
    <div class="bg-white rounded-lg shadow">
        <livewire:feature.study-program.tables.study-program-table />
    </div>

    <!-- Form Modal -->
    <dialog id="study_program_form_modal" class="modal" @if($showFormModal) open @endif>
        <div class="modal-box">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" wire:click="closeFormModal">✕</button>
            </form>

            <h3 class="font-bold text-lg mb-4">
                {{ $editingStudyProgramId ? 'Edit Program Studi' : 'Tambah Program Studi' }}
            </h3>

            <livewire:feature.study-program.forms.study-program-form
                :editingId="$editingStudyProgramId"
                :formData="$formData"
            />
        </div>

        <form method="dialog" class="modal-backdrop">
            <button wire:click="closeFormModal">close</button>
        </form>
    </dialog>
</div>

<script>
    Handle modal state with Alpine.js
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('showFormModal', () => {
            document.getElementById('study_program_form_modal').showModal();
        });

        Livewire.on('closeFormModal', () => {
            document.getElementById('study_program_form_modal').close();
        });
    });
</script>

