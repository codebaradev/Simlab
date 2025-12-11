<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Prodi"
        :breadcrumbs="[
            ['label' => 'Prodi', 'url' => '/prodi'],
            ['label' => $studyProgram ? $studyProgram->code : 'Tambah'],
        ]"
    />

    @if ($studyProgram)
        <div class="tabs tabs-lift">
            <a href="/prodi/{{ $studyProgram->id  }}" class="tab cursor-pointer tab-active" wire:navigate>Data Prodi</a>
            <div class="tab-content bg-base-100 border-base-300 p-6">
                <livewire:feature.study-program.forms.study-program-form
                :studyProgram="$studyProgram"
                />
            </div>

            <a href="/prodi/{{ $studyProgram->id}}/kelas" class="tab cursor-pointer" wire:navigate>Kelas</a>
        </div>
    @else
        <div class="card bg-base-100 border-base-300 p-6">
            <livewire:feature.study-program.forms.study-program-form
                :studyProgram="$studyProgram"
            />
        </div>
    @endif
</div>
