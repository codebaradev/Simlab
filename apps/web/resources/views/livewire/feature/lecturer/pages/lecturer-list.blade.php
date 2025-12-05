<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Dosenn"
        :breadcrumbs="[
            ['label' => 'Dosen'],
        ]"
    />

    <!-- Content -->
    <div class="bg-white rounded-lg shadow">
        <livewire:feature.lecturer.tables.lecturer-table />
    </div>
</div>
