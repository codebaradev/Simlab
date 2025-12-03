@props([
    'sticky' => true,
    'class' => '',
])

<div class="{{ $sticky ? 'sticky top-0 z-40' : '' }} bg-white border-b border-gray-200 {{ $class }}">
    {{ $slot }}
</div>

