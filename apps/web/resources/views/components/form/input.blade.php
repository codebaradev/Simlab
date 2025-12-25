@props([
    'name',
    'label' => null,
    'type' => 'text',
    'placeholder' => 'Masukan ' . ($label ?? $name),
    'leftIcon' => null,
    'rightIcon' => null,
    'live' => false,
    'required' => false,
    'disabled' => false,          // <-- NEW: Disabled state
    'readonly' => false,          // <-- NEW: Readonly state
    'helperText' => null,         // <-- NEW: Optional helper text
    'autocomplete' => 'off',      // <-- NEW: Autocomplete control
])

@php
    // Determine if component should be disabled/readonly
    $isDisabled = $disabled || $readonly;

    // Base classes
    $inputContainerClass = 'input w-full relative flex items-center';
    if ($isDisabled) {
        $inputContainerClass .= ' input-disabled cursor-not-allowed bg-base-200';
    }
    if ($readonly) {
        $inputContainerClass .= ' cursor-default bg-base-100';
    }

    // Input base classes
    $inputBaseClass = 'grow focus:outline-none';
    if ($isDisabled) {
        $inputBaseClass .= ' cursor-not-allowed opacity-70';
    }

    // Determine autocomplete value
    $autocompleteValue = $type === 'password' ? 'new-password' : $autocomplete;
@endphp

<div
    {{ $attributes->merge(['class' => "form-control w-full flex flex-col"]) }}
>
    {{-- Label dengan required indicator --}}
    @if ($label)
    <label class="label mb-1">
        <div class="flex items-center gap-1">
            <span class="label-text {{ $isDisabled ? 'opacity-70' : '' }}">
                {{ $label }}
                @if ($required)
                    <span class="text-red-500 text-sm font-normal">*</span>
                @endif
            </span>

            {{-- Optional badge for readonly/disabled state --}}
            {{-- @if($readonly)
                <span class="badge badge-sm badge-ghost ml-1">Readonly</span>
            @endif
            @if($disabled)
                <span class="badge badge-sm badge-ghost ml-1">Disabled</span>
            @endif --}}
        </div>
    </label>
    @endif

    {{-- Input Container --}}
    <label class="{{ $inputContainerClass }}">
        {{-- Left Icon --}}
        @if ($leftIcon)
        <div class="mr-2 {{ $isDisabled ? 'opacity-70' : '' }}">
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
            class="{{ $inputBaseClass }}"
            @if ($required && !$readonly) required @endif
            @if ($disabled) disabled @endif
            @if ($readonly) readonly @endif
            autocomplete="{{ $autocompleteValue }}"
            @if($readonly) onfocus="this.blur()" @endif
        />

        {{-- Right Icon Container --}}
        <div class="flex items-center text-gray-400">
            {{-- Right Icon --}}
            @if ($rightIcon)
            <div
                class="ml-2 {{ $isDisabled ? 'opacity-70' : '' }}"
                @if ($live)
                    wire:loading.class="hidden"
                    wire:target="{{ $name }}"
                @endif
                >
                @include('components.icon.' . $rightIcon, ['class' => "w-5 h-5"])
            </div>
            @endif

            {{-- Loading Spinner (only for live mode and not disabled) --}}
            @if ($live && !$isDisabled)
            <span
                class="loading loading-spinner size-4 ml-2 hidden"
                wire:loading.class.remove="hidden"
                wire:target="{{ $name }}"
            ></span>
            @endif

            {{-- Clear button for text inputs when not disabled/readonly --}}
            @if(in_array($type, ['text', 'email', 'number', 'tel', 'url']) && !$isDisabled && $live)
                <button
                    type="button"
                    x-show="$wire.{{ $name }}"
                    x-cloak
                    @click="$wire.set('{{ $name }}', '')"
                    class="ml-2 text-gray-400 hover:text-gray-600"
                    title="Clear"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </div>
    </label>

    {{-- Helper Text --}}
    @if($helperText && !$isDisabled)
        <div class="mt-1 text-xs text-gray-500">
            {{ $helperText }}
        </div>
    @endif

    {{-- Error Message --}}
    @error($name)
        <p class="text-xs mt-1 text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror

    {{-- Character counter for text inputs --}}
    {{-- @if(in_array($type, ['text', 'textarea']) && $live && !$isDisabled)
        <div class="mt-1 text-xs text-gray-500 text-right">
            <span x-text="$wire.{{ $name }}?.length || 0"></span>
            @if(isset($maxlength))
                / {{ $maxlength }}
            @endif
        </div>
    @endif --}}
</div>
