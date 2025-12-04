@props([
    'name',
    'label' => null,
    'placeholder' => 'Masukan ' . ($label ?? $name),
    'rows' => 3,
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

    {{-- Textarea --}}
    <textarea
        @if ($live)
        wire:model.live.debounce.300ms="{{ $name }}"
        @else
        wire:model="{{ $name }}"
        @endif
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        rows="{{ $rows }}"
        class="textarea textarea-bordered w-full"
        @if ($required) required @endif
    ></textarea>

    {{-- Error Message --}}
    @error($name)
        <p class="text-xs mt-1 text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
