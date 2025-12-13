<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Matakuliah"
        :breadcrumbs="[
            ['label' => 'Matakuliah', 'url' => '/matakuliah'],
            ['label' => $course->name , 'url' => '/matakuliah/' . $course->id],
            ['label' => 'Absensi']
        ]"
    />

    <div class="tabs tabs-lift">
        <a href="/matakuliah/{{ $course->id  }}" class="tab cursor-pointer" wire:navigate>Data Matakuliah</a>
        <a href="/matakuliah/{{ $course->id  }}/jadwal" class="tab cursor-pointer" wire:navigate>Jadwal</a>

        <a href="/matakuliah/{{ $course->id  }}/absensi" class="tab cursor-pointer tab-active" wire:navigate>Absensi</a>
        <div class="tab-content bg-base-100 border-base-300 p-6">
            <livewire:feature.course.tables.course-attendance-table/>
        </div>
    </div>
</div>
