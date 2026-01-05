<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Dosen"
        :breadcrumbs="[
            ['label' => 'Dosen', 'url' => '/dosen'],
            ['label' => $lecturer ? $lecturer->nip : 'Tambah'],
        ]"
    />

    @if ($lecturer)
        <div class="tabs tabs-lift">
            <a href="/dosen/{{ $lecturer->id  }}" class="tab cursor-pointer tab-active" wire:navigate>Data Diri</a>
            <div class="tab-content bg-base-100 border-base-300 p-6">
                <livewire:feature.lecturer.forms.lecturer-form
                :lecturer="$lecturer"
                />
            </div>
        </div>
    @else
        <div class="card bg-base-100 border-base-300 p-6">
            <livewire:feature.lecturer.forms.lecturer-form
                :lecturer="$lecturer"
            />
        </div>
    @endif
</div>
