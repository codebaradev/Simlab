<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Prodi"
        :breadcrumbs="[
            ['label' => 'Prodi', 'url' => '/prodi'],
            ['label' => $studyProgram->code, 'url' => '/prodi/' . $studyProgram->id],
            ['label' => 'kelas', 'url' => '/prodi/' . $studyProgram->id . '/kelas'],
            ['label' => $academicClass ? $academicClass->code : 'tambah']

        ]"
    />

    @if ($academicClass)
        <div class="tabs tabs-lift">
            <a href="/prodi/{{ $studyProgram->id}}/kelas/{{ $academicClass->id }}" class="tab cursor-pointer tab-active" wire:navigate>Data Kelas</a>
            <div class="tab-content bg-base-100 border-base-300 p-6">
                <livewire:feature.academic-class.forms.academic-class-form
                    :studyProgram="$studyProgram"
                    :academicClass="$academicClass"
                />
            </div>

            <a href="/prodi/{{ $studyProgram->id}}/kelas/{{ $academicClass->id }}/mahasiswa" class="tab cursor-pointer" wire:navigate>Mahasiswa</a>
        </div>
    @else
        <div class="card bg-base-100 border-base-300 p-6">
            <livewire:feature.academic-class.forms.academic-class-form
                :studyProgram="$studyProgram"
                :academicClass="$academicClass"
            />
        </div>
    @endif
</div>
