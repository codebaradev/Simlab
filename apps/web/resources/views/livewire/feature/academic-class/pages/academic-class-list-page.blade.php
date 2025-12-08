<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Prodi"
        :breadcrumbs="[
            ['label' => 'Prodi', 'url' => '/prodi'],
            ['label' => $studyProgram ? $studyProgram->code : 'Tambah'],
        ]"
    />

    <div class="tabs tabs-lift">
        <a href="/prodi/{{ $studyProgram->id  }}" class="tab cursor-pointer" wire:navigate>Data Prodi</a>

        <a href="/prodi/{{ $studyProgram->id}}/kelas" class="tab cursor-pointer tab-active" wire:navigate>Kelas</a>
        <div class="tab-content bg-base-100 border-base-300 p-6">
            <livewire:feature.academic-class.forms.academic-class-form
            :studyProgram="$studyProgram"
            />
        </div>
    </div>
</div>
