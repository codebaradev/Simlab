<div class="p-8">

    <x-page.header
        class="mb-4"
        title="Jadwal"
        :breadcrumbs="[
            ['label' => 'Jadwal', 'url' => '/jadwal'],
            ['label' => 'Request'],
        ]"
    />

    <div class="tabs tabs-lift">
        <a href="/jadwal" class="tab cursor-pointer" wire:navigate>Jadwal</a>

        <a href="/jadwal/request" class="tab cursor-pointer tab-active" wire:navigate>Request</a>
        <div class="tab-content bg-base-100 border-base-300 p-4">
            <livewire:feature.schedule.lists.request-list/>
        </div>
    </div>
</div>
