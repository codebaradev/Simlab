@props([
    'class' => '',
])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 {{ $class }}">
    {{ $slot }}
</div>

