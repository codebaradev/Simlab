@props([
    'name' => 'sidebar-menu',
    'url' => '/',
    'icon' => 'dashboard',
    'selected' => null
])

@php
    if ($selected) {
        $isCurrent = request()->is($selected);
    } else {
        $isCurrent = request()->is(ltrim($url, '/'));
    }
@endphp

<!-- Mahasiswa -->
<li>
    <a
        href="{{ $url }}"
        @class([
            'flex items-center p-3 rounded-lg hover:bg-blue-50 transition-colors',
            'active-menu text-red-50' => $isCurrent
        ])
        wire:navigate
    >
        @component("components.icon.$icon", ['class' => 'size-6'])@endcomponent
        <span class="ml-3" x-show="!isCollapsed">{{ $name }}</span>
    </a>
</li>
