@props([
    'title',
    // 'subTitle' => null
])

<div {{  $attributes->merge(['class' => ''])  }}>
    <h1 class="text-3xl font-bold text-gray-800 capitalize">{{ $title }}</h1>
    {{-- @if ($subTitle)
        <p class="text-gray-600 mt-2">{{ $subTitle }}</p>
    @endif --}}
</div>
