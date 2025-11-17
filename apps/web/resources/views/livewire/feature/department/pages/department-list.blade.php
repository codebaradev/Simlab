<div class="p-8">
    <x-page.breadcrumbs
        :items="[
            ['label' => 'Jurusan'],
        ]"
    />

    <x-page.title
        title="Jurusan"
    />

    <!-- Content -->
    <div class="bg-white rounded-lg shadow">
        <livewire:feature.department.tables.department-table />
    </div>

    <!-- Form Modal -->
    <dialog id="department_form_modal" class="modal" @if($showFormModal) open @endif>
        <div class="modal-box">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" wire:click="closeFormModal">✕</button>
            </form>

            <h3 class="font-bold text-lg mb-4">
                {{ $editingDepartment ? 'Edit Jurusan' : 'Tambah Jurusan' }}
            </h3>

            <livewire:feature.department.forms.department-form
                :editingId="$editingDepartment"
                :formData="$formData"
                key="{{ $editingDepartment ? 'edit-' . $editingDepartment : 'create' }}"
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
            document.getElementById('department_form_modal').showModal();
        });

        Livewire.on('closeFormModal', () => {
            document.getElementById('department_form_modal').close();
        });
    });
</script>
