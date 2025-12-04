<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Mahasiswa"
        :breadcrumbs="[
            ['label' => 'Mahasiswa'],
        ]"
    />

    <!-- Content -->
    <div class="bg-white rounded-lg shadow">
        <livewire:feature.student.tables.student-table />
    </div>
</div>
