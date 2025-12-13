@props([
    'name',
    'label' => null,
    'placeholder' => 'Pilih tanggal',
    'min' => null,
    'max' => null,
    'live' => false,
    'required' => false
])

<div
    {{ $attributes->merge(['class' => "form-control w-full flex flex-col"]) }}
>
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

    {{-- Input Date Container --}}
    <label class="input w-full relative flex items-center">
        <input
            @if ($live)
            wire:model.live.debounce.300ms="{{ $name }}"
            @else
            wire:model="{{ $name }}"
            @endif
            name="{{ $name }}"
            type="time"
            placeholder="{{ $placeholder }}"
            @if($min) min="{{ $min }}" @endif
            @if($max) max="{{ $max }}" @endif
            class="grow"
            @if ($required) required @endif
        />
    </label>

    {{-- Error Message --}}
    @error($name)
        <p class="text-xs mt-1 text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
