@props([
    'name',
    'label' => null,
    'options' => [],
    'placeholder' => 'Pilih ' . ($label ?? $name),
    'optionValue' => 'id',
    'optionLabel' => 'name',
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

    {{-- Select --}}
    <select
        @if ($live)
        wire:model.live="{{ $name }}"
        @else
        wire:model="{{ $name }}"
        @endif
        name="{{ $name }}"
        class="select select-bordered w-full"
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
</div>

