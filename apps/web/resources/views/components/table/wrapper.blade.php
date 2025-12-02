@props([
    'class' => '',
])

<div class="bg-white rounded-md shadow-sm overflow-hidden {{ $class }}">
    {{ $slot }}
</div>

