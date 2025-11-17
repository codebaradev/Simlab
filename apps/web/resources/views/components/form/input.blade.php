@props([
    'name',
    'label' => null,
    'type' => 'text',
    'placeholder' => 'Masukan ' . ($label ?? $name),
    'leftIcon' => null,
    'rightIcon' => null,
    'live' => false
])

<div
    {{ $attributes->merge(['class' => "form-control w-full flex flex-col"]) }}
>
    {{-- Label --}}
    @if ($label)
    <label class="label mb-1">
        <span class="label-text">{{ $label }}</span>
    </label>
    @endif

    {{-- Input --}}
    <label class="input w-full">
        {{-- Specific icon component (e.g. resources/views/components/icons/search.blade.php) --}}
        @if ($leftIcon)
        <div>
            @include('components.icon.' . $leftIcon, ['class' => "w-5 h-5 text-gray-400"])
        </div>
        @endif

        <input
            @if ($live)
            wire:model.live.debounce.300ms="{{ $name }}"
            @else
            wire:model="{{ $name }}"
            @endif
            name="{{ $name }}"
            type="{{ $type }}"
            placeholder="{{ $placeholder }}"
            placeholder="Enter your username"
            class="grow"
        />

        @if ($rightIcon)
        <div>
            @include('components.icon.' . $rightIcon, ['class' => "w-5 h-5 text-gray-400"])
        </div>
        @endif
    </label>



    {{-- Error Message --}}
    @error($name)
        <p class="text-xs mt-1 text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
