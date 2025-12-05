<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Ruangan"
        :breadcrumbs="[
            ['label' => 'Ruangan', 'url' => '/ruangan'],
            ['label' => $room ? $room->code : 'Tambah'],
        ]"
    />

    @if ($room)
        <div class="tabs tabs-lift">
            <a href="/ruangan/{{ $room->id  }}" class="tab cursor-pointer tab-active" wire:navigate>Data Ruangan</a>
            <div class="tab-content bg-base-100 border-base-300 p-6">
                <livewire:feature.room.forms.room-form
                    :room="$room"
                />
            </div>
        </div>
    @else
        <div class="card bg-base-100 border-base-300 p-6">
            <livewire:feature.room.forms.room-form
                :room="$room"
            />
        </div>
    @endif
</div>
