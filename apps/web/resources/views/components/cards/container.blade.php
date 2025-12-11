@props([
    'class' => '',
])

<div class=" {{ $class }}">
    <div class="p-6 grid grid-cols-3 gap-6">
        {{ $slot }}
    </div>
</div>
