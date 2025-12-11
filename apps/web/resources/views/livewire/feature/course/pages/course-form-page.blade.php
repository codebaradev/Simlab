<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Matakuliah"
        :breadcrumbs="[
            ['label' => 'Matakuliah', 'url' => '/matakuliah'],
            ['label' => $course ? $course->name : 'Tambah'],
        ]"
    />

    @if ($course)
        <div class="tabs tabs-lift">
            <a href="/matakuliah/{{ $course->id  }}" class="tab cursor-pointer tab-active" wire:navigate>Data Matakuliah</a>
            <div class="tab-content bg-base-100 border-base-300 p-6">
                <livewire:feature.course.forms.course-form
                :course="$course"
                />
            </div>

            <a href="/matakuliah/{{ $course->id  }}/jadwal" class="tab cursor-pointer" wire:navigate>Jadwal</a>
        </div>
    @else
        <div class="card bg-base-100 border-base-300 p-6">
            <livewire:feature.course.forms.course-form/>
        </div>
    @endif
</div>
