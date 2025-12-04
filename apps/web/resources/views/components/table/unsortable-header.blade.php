@props([
    'label' => '',
    'class' => '',
])

<th class="cursor-pointer {{ $class }}">
    <div class="flex items-center gap-1">
        <span>{{ $label }}</span>
    </div>
</th>

