@props([
    'items' => [],
])

<nav {{ $attributes->merge(['class' => 'text-sm breadcrumbs']) }}>
    <ul>
        @foreach($items as $index => $item)
            @php
                $isLast = $loop->last;
                $hasUrl = isset($item['url']);
            @endphp

            <li>
                @if($hasUrl && !$isLast)
                    <a href="{{ $item['url'] }}" class="text-gray-600 hover:text-primary">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-gray-900 font-medium">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ul>
</nav>
