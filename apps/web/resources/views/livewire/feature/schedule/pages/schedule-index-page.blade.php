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

        <a href="/jadwal/request" class="tab cursor-pointer" wire:navigate>Request</a>
    </div>

    <!-- Form Modal -->
    <dialog id="request_modal" class="modal" @if($showRequestFormModal) open @endif>
        <div class="modal-box w-3/4 max-w-5xl max-h-[90vh]">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" wire:click="closeRequestFormModal">✕</button>
            </form>

            <h3 class="font-bold text-lg mb-4">Request Jadwal</h3>

            <livewire:feature.schedule.forms.request-schedule-form
                key="request-schedule-form"
            />
        </div>

        <form method="dialog" class="modal-backdrop">
            <button wire:click="closeRequestFormModal">close</button>
        </form>
    </dialog>
</div>
