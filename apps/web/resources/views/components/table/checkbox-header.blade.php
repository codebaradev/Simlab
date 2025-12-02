@props([
    'class' => 'w-12',
])

<th class="{{ $class }}">
    <label>
        <input type="checkbox" class="checkbox checkbox-sm" wire:model.live="selectAll" />
    </label>
</th>

