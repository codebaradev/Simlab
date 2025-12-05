<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Ruangan"
        :breadcrumbs="[
            ['label' => 'Ruangan'],
        ]"
    />

    <!-- Content -->
    <div class="bg-white rounded-lg shadow">
        <livewire:feature.room.tables.room-table />
    </div>
</div>
