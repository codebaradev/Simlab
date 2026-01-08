<x-filament-panels::page>
    <form wire:submit="authenticate" class="space-y-8">
        {{ $this->form }}

        {{ $this->authenticateAction }}
    </form>
</x-filament-panels::page>
