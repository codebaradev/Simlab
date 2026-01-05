@props([
    'name',
    'label' => null,
    'options' => [],
    'placeholder' => 'Pilih ' . ($label ?? $name),
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'live' => false,
    'required' => false
])

<div {{ $attributes->merge(['class' => 'form-control w-full flex flex-col']) }}>
    {{-- Label --}}
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

    {{-- Multi Select --}}
    <select
        multiple
        name="{{ $name }}[]"
        class="select select-bordered w-full min-h-[3rem]"
        @if ($live)
            wire:model.live="{{ $name }}"
        @else
            wire:model="{{ $name }}"
        @endif
        @if ($required) required @endif
    >
        @foreach ($options as $option)
            @php
                $optionVal = is_array($option)
                    ? $option[$optionValue]
                    : $option->{$optionValue};

                $optionLab = is_array($option)
                    ? $option[$optionLabel]
                    : $option->{$optionLabel};
            @endphp

            <option value="{{ $optionVal }}">
                {{ $optionLab }}
            </option>
        @endforeach
    </select>

    {{-- Helper text --}}
    <span class="text-xs text-gray-500 mt-1">
        Tahan <kbd class="kbd kbd-xs">Ctrl</kbd> / <kbd class="kbd kbd-xs">Cmd</kbd> untuk memilih lebih dari satu
    </span>

    {{-- Error --}}
    @error($name)
        <p class="text-xs mt-1 text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
