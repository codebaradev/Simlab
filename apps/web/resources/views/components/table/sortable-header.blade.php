@props([
    'field' => '',
    'label' => '',
    'sortField' => '',
    'sortDirection' => 'asc',
    'class' => '',
])

<th class="cursor-pointer {{ $class }}" wire:click="sortBy('{{ $field }}')">
    <div class="flex items-center gap-1">
        <span>{{ $label }}</span>
        @if($sortField === $field)
            <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
            </svg>
        @endif
    </div>
</th>

