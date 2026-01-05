@props([
    'class' => '',
])

<thead class="sticky top-0 z-30 bg-white {{ $class }}">
    {{ $slot }}
</thead>

