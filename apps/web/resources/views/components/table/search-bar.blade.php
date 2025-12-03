@props([
    'name' => 'search',
    'placeholder' => 'Cari...',
    'class' => '',
])

<div class="flex-1 max-w-md {{ $class }}">
    <x-form.input
        name="{{ $name }}"
        rightIcon="search"
        class="max-w-xs"
        :live="true"
        :placeholder="$placeholder"
    />
</div>

