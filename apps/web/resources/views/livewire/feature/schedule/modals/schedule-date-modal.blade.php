<div x-data="{ open: @entangle('show') }" x-cloak>
    <div
        x-show="open"
        class="fixed inset-0 z-50 flex items-center justify-center"
        style="background: rgba(0,0,0,0.4);"
    >
        <div class="bg-white rounded-lg shadow-lg max-w-xl w-full mx-4" @click.away="open = false">
            <div class="p-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-medium">Detail tanggal: {{ $date }}</h3>
                <button type="button" class="btn btn-ghost btn-sm" @click="open = false" wire:click="close">Tutup</button>
            </div>

            <div class="p-4">
                @if(count($events))
                    <ul class="space-y-3">
                        @foreach($events as $ev)
                            <li class="p-3 border rounded">
                                <div class="text-sm font-semibold">{{ $ev->nim ?? ('ID: ' . $ev->id) }}</div>
                                <div class="text-xs text-gray-600">{{ $ev->generation ?? $ev->created_at }}</div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center text-sm text-gray-500">
                        Tidak ada event pada tanggal ini.
                    </div>
                @endif
            </div>

            <div class="p-3 border-t text-right">
                <button type="button" class="btn btn-sm" @click="open = false" wire:click="close">Tutup</button>
            </div>
        </div>
    </div>
</div>
