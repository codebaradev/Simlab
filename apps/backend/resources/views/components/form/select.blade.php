@props([
    'name',
    'label' => null,
    'options' => [],
    'placeholder' => 'Pilih ' . ($label ?? $name),
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'live' => false,
    'required' => false,
    'readonly' => false,
    'disabled' => false,
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

    {{-- Select --}}
    <select
        @if ($live)
        wire:model.live="{{ $name }}"
        @else
        wire:model="{{ $name }}"
        @endif
        name="{{ $name }}"
        class="select select-bordered w-full {{ ($readonly || $disabled) ? 'input-disabled cursor-not-allowed bg-base-200' : '' }}"
        @if ($required) required @endif
        @if ($disabled) disabled @endif
        @if ($readonly) tabindex="-1" @endif
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $option)
            @php
                $optionVal = is_array($option) ? $option[$optionValue] : $option->{$optionValue};
                $optionLab = is_array($option) ? $option[$optionLabel] : $option->{$optionLabel};
            @endphp
            <option value="{{ $optionVal }}">
                {{ $optionLab }}
            </option>
        @endforeach
    </select>

    {{-- Error Message --}}
    @error($name)
        <p class="text-xs mt-1 text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror

    @if ($readonly && !$disabled)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const el = document.querySelector('[name="{{ $name }}"]');
                el?.addEventListener('mousedown', e => e.preventDefault());
            });
        </script>
    @endif
</div>
