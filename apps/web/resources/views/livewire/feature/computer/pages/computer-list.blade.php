<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Ruangan"
        :breadcrumbs="[
            ['label' => 'Ruangan', 'url' => '/ruangan'],
            ['label' => $room->code, 'url' => '/ruangan/'. $room->id],
            ['label' => 'Komputer '],
        ]"
    />

    <div class="tabs tabs-lift">
        <a href="/ruangan/{{ $room->id  }}" class="tab cursor-pointer" wire:navigate>Data Ruangan</a>

        <a href="/ruangan/{{ $room->id  }}/komputer" class="tab cursor-pointer tab-active" wire:navigate>Komputer</a>
        <div class="tab-content">
            <livewire:feature.computer.tables.computer-table
                :room="$room"
            />
        </div>
        <a href="/ruangan/{{ $room->id  }}/aplikasi" class="tab cursor-pointer" wire:navigate>Aplikasi</a>
    </div>
</div>
