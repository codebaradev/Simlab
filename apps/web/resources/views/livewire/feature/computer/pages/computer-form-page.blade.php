<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Ruangan"
        :breadcrumbs="[
            ['label' => 'Ruangan', 'url' => '/ruangan'],
            ['label' => $room->code, 'url' => '/ruangan/'. $room->id],
            ['label' => 'Komputer', 'url' => '/ruangan/'. $room->id . '/komputer'],
            ['label' => $computer ? $computer->name : 'Tambah'],
        ]"
    />

    <div class="card bg-base-100 border-base-300 p-6">
        <livewire:feature.computer.forms.computer-form
            :room="$room"
            :computer="$computer"
        />
    </div>
</div>
