<!-- ...existing code... -->
<div class="flex items-center justify-between mb-3">
    <div class="flex items-center gap-2">
        <!-- panggil method pada komponen kalender langsung -->
        <x-button type="button" class="btn btn-sm" wire:click="previousMonth" title="Bulan sebelumnya">
            ‹
        </x-button>

        <x-button type="button" class="btn btn-sm" wire:click="goToToday" title="Hari ini" variant="outline">
            Hari Ini
        </x-button>

        <x-button type="button" class="btn btn-sm" wire:click="nextMonth" title="Bulan berikutnya">
            ›
        </x-button>
    </div>

    <div class="text-lg font-bold">
        {{-- gunakan startsAt yang disediakan oleh livewire-calendar --}}
        <span>{{ $this->startsAt->translatedFormat('F Y') }}</span>
    </div>

    <div>
        <x-button leftIcon="add" target="$dispatch('showRequestFormModal')" wire:click="$dispatch('showRequestFormModal')">
            Request Jadwal
        </x-button>
    </div>
</div>
<!-- ...existing code... -->
