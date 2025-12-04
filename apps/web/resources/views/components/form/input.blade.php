@props([
    'name',
    'label' => null,
    'type' => 'text',
    'placeholder' => 'Masukan ' . ($label ?? $name),
    'leftIcon' => null,
    'rightIcon' => null,
    'live' => false,
    'required' => false
])

<div
    {{ $attributes->merge(['class' => "form-control w-full flex flex-col"]) }}
>
    {{-- Label dengan required indicator --}}
    @if ($label)
    <label class="label mb-1">
        <div class="flex items-center gap-1">
            <span class="label-text">{{ $label }}</span>
            @if ($required)
                <span class="text-red-500 text-sm font-normal">*</span>
            @endif
        </div>
    </label>
    @endif

    {{-- Input Container --}}
    <label class="input w-full relative flex items-center">
        {{-- Left Icon --}}
        @if ($leftIcon)
        <div class="mr-2">
            @include('components.icon.' . $leftIcon, ['class' => "w-5 h-5 text-gray-400"])
        </div>
        @endif

        {{-- Input Field --}}
        <input
            @if ($live)
            wire:model.live.debounce.300ms="{{ $name }}"
            @else
            wire:model="{{ $name }}"
            @endif
            name="{{ $name }}"
            type="{{ $type }}"
            placeholder="{{ $placeholder }}"
            class="grow"
            @if ($required) required @endif
        />

        {{-- Right Icon Container --}}
        <div class="flex items-center text-gray-400">
            {{-- Right Icon --}}
            @if ($rightIcon)
            <div
                class="ml-2"
                @if ($live)
                    wire:loading.class="hidden"
                    wire:target="{{ $name }}"
                @endif
                >
                @include('components.icon.' . $rightIcon, ['class' => "w-5 h-5"])
            </div>
            @endif

            {{-- Loading Spinner (only for live mode) --}}
            @if ($live)
            <span
                class="loading loading-spinner size-4 ml-2 hidden"
                wire:loading.class.remove="hidden"
                wire:target="{{ $name }}"
            ></span>
            @endif
        </div>
    </label>

    {{-- Error Message --}}
    @error($name)
        <p class="text-xs mt-1 text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
