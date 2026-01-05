{{-- resources/views/components/ui/button.blade.php --}}
@props([
    'type' => 'button',
    'variant' => 'primary',
    // Nama target Livewire (method / property) yang ingin dipantau oleh wire:loading.
    // Jika null, wire:loading akan bereaksi ke semua target (global).
    'target' => null,
    'leftIcon' => null,
    'rightIcon' => null,
])

@php
    $base = 'btn inline-flex items-center gap-2';
    $variants = [
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'ghost' => 'btn-ghost',
        'outline' => 'btn-outline',
        'cancel' => 'btn-outline btn-secondary',
        'error' => 'btn-error',
    ];
    $variantClass = $variants[$variant] ?? $variants['primary'];

    // helper untuk menambahkan atribut wire:target jika ada
    $targetAttr = $target ? ' wire:target="'.$target.'"' : '';
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => trim("$base $variantClass")]) }}


    @if ($target)
        {{-- disable saat loading --}}
        wire:loading.attr="disabled" {!! $targetAttr !!}
        {{-- aria busy saat loading --}}
        wire:loading.class.remove="not-loading" wire:loading.class="is-loading" {!! $targetAttr !!}
    @endif

    aria-live="polite"
>

    {{-- TEXT: show when NOT loading; hidden when loading --}}
    <span class="flex items-center gap-1" >
        {{-- LOADING SPINNER (only visible during Livewire loading of target) --}}
        @if ($target)
        <span
            class="loading loading-spinner size-4"
            wire:loading{!! $targetAttr !!} wire:loading.class.remove="hidden"
        ></span>
        @endif

        @if ($leftIcon)
        <div
            class=""
            @if ($target)
                wire:loading.class="hidden"
                wire:target="{{ $target }}"
            @endif
        >
            @include('components.icon.' . $leftIcon)
        </div>
        @endif

        {{ $slot }}

        @if ($rightIcon)
        <div>
            @include('components.icon.' . $rightIcon)
        </div>
        @endif
    </span>


</button>
