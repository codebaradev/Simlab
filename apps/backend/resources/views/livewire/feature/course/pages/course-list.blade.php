<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Matakuliah"
        :breadcrumbs="[
            ['label' => 'Matakuliah'],
        ]"
    />

    <!-- Content -->
    <div class="bg-white rounded-lg shadow">
        <livewire:feature.course.cards.course-card-list />
    </div>
</div>
