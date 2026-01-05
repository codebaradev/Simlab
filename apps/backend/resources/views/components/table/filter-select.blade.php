@props([
    'name',
    'label' => null,
    'options' => [],
    'placeholder' => 'Semua',
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'class' => '',
])

<div class="form-control {{ $class }}">
    @if ($label)
        <label class="text-sm font-medium whitespace-nowrap mb-2">{{ $label }}:</label>
    @endif
    <select
        wire:model.live="{{ $name }}"
        name="{{ $name }}"
        class="select select-bordered select-sm min-w-[150px]"
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $option)
            @php
                $optionVal = is_array($option) ? $option[$optionValue] : $option->{$optionValue};
                $optionLab = is_array($option) ? $option[$optionLabel] : $option->{$optionLabel};
            @endphp
            <option value="{{ $optionVal }}">
                {{ $optionLab }}
            </option>
        @endforeach
    </select>
</div>

