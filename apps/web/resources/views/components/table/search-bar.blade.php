@props([
    'name' => 'search',
    'placeholder' => 'Cari...',
    'class' => '',
    'inputClass' => 'max-w-xs'
])

<div class="flex-1 max-w-md {{ $class }}">
    <x-form.input
        name="{{ $name }}"
        rightIcon="search"
        class="{{ $inputClass }}"
        :live="true"
        :placeholder="$placeholder"
    />
</div>

