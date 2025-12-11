<!-- Main Content -->
<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Jadwal"
        :breadcrumbs="[
            ['label' => 'Jadwal'],
        ]"
    />

    <div class="tabs tabs-lift">
        <a href="/jadwal" class="tab cursor-pointer tab-active" wire:navigate>Jadwal</a>
        <div class="tab-content bg-base-100 border-base-300 p-4">
            <livewire:feature.schedule.calendars.schedule-calendar
                week-starts-at="1"
                before-calendar-view="components/calendars/before"
                after-calendar-view="components/calendars/after"
            />
        </div>
    </div>
</div>
