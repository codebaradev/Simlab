<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Prodi"
        :breadcrumbs="[
            ['label' => 'Prodi', 'url' => '/prodi'],
            ['label' => $studyProgram->code, 'url' => '/prodi/' . $studyProgram->id],
            ['label' => 'kelas', 'url' => '/prodi/' . $studyProgram->id . '/kelas'],
            ['label' => $academicClass->code, 'url' => '/prodi/' . $studyProgram->id . '/kelas/' . $academicClass->id],
            ['label' => 'mahasiswa']

        ]"
    />

    <div class="tabs tabs-lift">
        <a href="/prodi/{{ $studyProgram->id}}/kelas/{{ $academicClass->id }}" class="tab cursor-pointer " wire:navigate>Data Kelas</a>

        <a href="/prodi/{{ $studyProgram->id}}/kelas/{{ $academicClass->id }}/mahasiswa" class="tab cursor-pointer tab-active" wire:navigate>Mahasiswa</a>
        <div class="tab-content">
            <livewire:feature.academic-class.tables.student-table
                :studyProgram="$studyProgram"
                :academicClass="$academicClass"
            />
        </div>
    </div>

    <div>

    </div>

    <dialog id="add_student_modal" class="modal" @if($showFormModal) open @endif>
        <div class="modal-box h-[90vh]">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" wire:click="closeFormModal">✕</button>
            </form>

            <h3 class="font-bold text-lg mb-4">Tambah Mahasiswa</h3>

            <livewire:feature.academic-class.tables.add-student-table
                :spId="$studyProgram->id"
                :acId="$academicClass->id"
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
            document.getElementById('add_student_modal').showModal();
        });

        Livewire.on('closeFormModal', () => {
            document.getElementById('add_student_modal').close();
        });
    });
</script>
