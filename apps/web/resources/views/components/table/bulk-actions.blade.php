@props([
    'selected' => [],
    'itemName' => 'item',
    'actions' => [], // daftar action fleksibel
    'class' => '',
])

@if(count($selected) > 0)
    <div class="p-3 border-t border-gray-100 bg-warning/10 {{ $class }}">
        <div class="flex items-center gap-3">

            {{-- Info jumlah item terpilih --}}
            <span class="text-sm text-warning">
                {{ count($selected) }} {{ $itemName }} dipilih
            </span>

            {{-- Render semua action --}}
            @foreach($actions as $action)
                @php
                    $label   = $action['label']   ?? 'Action';
                    $click   = $action['action']  ?? null;
                    $confirm = $action['confirm'] ?? null;
                    $btnClass= $action['class']   ?? 'btn-primary';
                @endphp

                <button
                    type="button"
                    class="btn btn-sm {{ $btnClass }}"
                    @if($click) wire:click="{{ $click }}" @endif
                    @if($confirm) wire:confirm="{{ $confirm }}" @endif
                >
                    <span wire:loading wire:target="{{ $click  }}">
                        <span class="loading loading-spinner loading-sm"></span>
                    </span>

                    <span>{{ $label }}</span>
                </button>
            @endforeach

            {{-- Tombol batal --}}
            <button
                type="button"
                wire:click="clearSelection"
                class="btn btn-ghost btn-sm"
            >
                <span wire:loading wire:target="clearSelection">
                    <span class="loading loading-spinner loading-sm"></span>
                </span>

                <span>Batal</span>
            </button>

        </div>
    </div>
@endif
