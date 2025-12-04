@props([
    'value' => '',
    'class' => '',
])

<td class="{{ $class }}" @click.stop>
    <label>
        <input type="checkbox" class="checkbox checkbox-sm" wire:model.live="selected" value="{{ $value }}" />
    </label>
</td>

