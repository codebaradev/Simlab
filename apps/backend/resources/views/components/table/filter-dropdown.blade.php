@props([
    'filters' => [], // Array of filter configurations
    'activeCount' => 0, // Number of active filters (should be calculated in component)
    'label' => 'Filter',
    'icon' => 'funnel',
    'dropdownPosition' => 'end',
    'class' => '',
])

<div class="dropdown dropdown-{{ $dropdownPosition }} {{ $class }}">
    <label tabindex="0" class="btn btn-outline gap-2">
        @if(isset($icon))
            @include('components.icon.' . $icon, ['class' => 'size-4'])
        @endif
        {{ $label }}
        @if($activeCount > 0)
            <span class="badge badge-primary badge-sm">{{ $activeCount }}</span>
        @endif
    </label>

    <div tabindex="0" class="dropdown-content z-[50] menu p-4 shadow-lg bg-base-100 rounded-box w-80 mt-2 border border-base-300">
        <div class="space-y-4">
            @foreach($filters as $filter)
                @php
                    $filterName = $filter['name'] ?? '';
                    $filterLabel = $filter['label'] ?? ucfirst($filterName);
                    $filterOptions = $filter['options'] ?? [];
                    $optionValue = $filter['optionValue'] ?? 'id';
                    $optionLabel = $filter['optionLabel'] ?? 'name';
                    $placeholder = $filter['placeholder'] ?? 'Semua';
                @endphp


                <x-table.filter-select
                    :name="$filterName"
                    :label="$filterLabel"
                    :options="$filterOptions"
                    :placeholder="$placeholder"
                    :optionValue="$optionValue"
                    :optionLabel="$optionLabel"
                />

                {{-- <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">{{ $filterLabel }}</span>
                    </label>
                    <select
                        wire:model.live="{{ $filterName }}"
                        name="{{ $filterName }}"
                        class="select select-bordered select-sm w-full"
                    >
                        <option value="">{{ $placeholder }}</option>
                        @foreach($filterOptions as $option)
                            @php
                                $optionVal = is_array($option) ? $option[$optionValue] : $option->{$optionValue};
                                $optionLab = is_array($option) ? $option[$optionLabel] : $option->{$optionLabel};
                            @endphp
                            <option value="{{ $optionVal }}">
                                {{ $optionLab }}
                            </option>
                        @endforeach
                    </select>
                </div> --}}
            @endforeach

            {{-- Clear Filters Button --}}
            @if($activeCount > 0)
                <div class="pt-2 border-t border-base-300">
                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="btn btn-ghost btn-sm w-full"
                    >
                        Reset Filter
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

