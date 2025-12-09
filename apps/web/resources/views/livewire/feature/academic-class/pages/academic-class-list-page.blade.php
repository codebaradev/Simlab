<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Prodi"
        :breadcrumbs="[
            ['label' => 'Prodi', 'url' => '/prodi'],
            ['label' => $studyProgram->code, 'url' => '/prodi/' . $studyProgram->id],
            ['label' => 'kelas'],
        ]"
    />

    <div class="tabs tabs-lift">
        <a href="/prodi/{{ $studyProgram->id  }}" class="tab cursor-pointer" wire:navigate>Data Prodi</a>

        <a href="/prodi/{{ $studyProgram->id}}/kelas" class="tab cursor-pointer tab-active" wire:navigate>Kelas</a>
        <div class="tab-content">
            <livewire:feature.academic-class.tables.academic-class-table
                :studyProgram="$studyProgram"
            />
        </div>
    </div>
</div>
