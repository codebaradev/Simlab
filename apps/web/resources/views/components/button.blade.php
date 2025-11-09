{{-- resources/views/components/ui/button.blade.php --}}
@props([
    'type' => 'button',
    'variant' => 'primary',
    // Nama target Livewire (method / property) yang ingin dipantau oleh wire:loading.
    // Jika null, wire:loading akan bereaksi ke semua target (global).
    'livewireTarget' => null,
])

@php
    $base = 'btn inline-flex items-center rounded-lg gap-2';
    $variants = [
    'primary' => 'btn-primary',
    'secondary' => 'btn-secondary',
    'ghost' => 'btn-ghost',
    'outline' => 'btn-outline',
    ];
    $variantClass = $variants[$variant] ?? $variants['primary'];

    // helper untuk menambahkan atribut wire:target jika ada
    $targetAttr = $livewireTarget ? ' wire:target="'.$livewireTarget.'"' : '';
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => trim("$base $variantClass")]) }}
    {{-- disable saat loading --}}
    wire:loading.attr="disabled" {!! $targetAttr !!}
    {{-- aria busy saat loading --}}
    wire:loading.class.remove="not-loading" wire:loading.class="is-loading" {!! $targetAttr !!}
    aria-live="polite"
>

    {{-- LOADING SPINNER (only visible during Livewire loading of target) --}}
    <span class="flex-none" role="status" aria-hidden="true"
          style="display: none;"
          wire:loading{!! $targetAttr !!} wire:loading.class.remove="hidden" >


        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </span>

    {{-- TEXT: show when NOT loading; hidden when loading --}}
    <span class="flex-1" wire:loading.remove{!! $targetAttr !!}>
        {{ $slot }}
    </span>
</button>
