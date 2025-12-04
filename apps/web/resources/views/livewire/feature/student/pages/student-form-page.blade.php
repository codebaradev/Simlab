<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Mahasiswa"
        :breadcrumbs="[
            ['label' => 'Mahasiswa', 'url' => '/mahasiswa'],
            ['label' => $student ? $student->nim : 'Tambah'],
        ]"
    />

    @if ($student)
        <div class="tabs tabs-lift">
            <a href="/mahasiswa/{{ $student->id  }}" class="tab cursor-pointer tab-active" wire:navigate>Data Diri</a>
            <div class="tab-content bg-base-100 border-base-300 p-6">
                <livewire:feature.student.forms.student-form
                :student="$student"
                />
            </div>
        </div>
    @else
        <div class="card bg-base-100 border-base-300 p-6">
            <livewire:feature.student.forms.student-form
                :student="$student"
            />
        </div>
    @endif
</div>
