@props([
    'name',
    'class' => '',
    'label' => null,
    'options' => [],
    'placeholder' => 'Cari dan pilih ' . ($label ?? $name),
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'live' => false,
    'required' => false,
    'noResults' => 'Tidak ada kelas yang cocok',
    'actionLabel' => null,
    'actionEvent' => null,
    'disabled' => false, // <-- NEW: Disabled state
    'readonly' => false, // <-- NEW: Readonly state
])

@php
    $items = collect($options)->map(function($o) use ($optionValue, $optionLabel) {
        if (is_array($o)) {
            $val = $o[$optionValue] ?? null;
            $lab = $o[$optionLabel] ?? null;
        } else {
            $val = $o->{$optionValue} ?? null;
            $lab = $o->{$optionLabel} ?? null;
        }
        return ['id' => $val, 'label' => $lab];
    })->values();

    // Get initial value from Livewire context
    $initialValue = $this->__livewire['data'][$name] ?? null;
    $initialLabel = '';
    if ($initialValue) {
        $found = $items->firstWhere('id', $initialValue);
        $initialLabel = $found ? $found['label'] : '';
    }

    // Determine if component should be disabled/readonly
    $isDisabled = $disabled || $readonly;
    $inputClass = 'input input-bordered w-full pr-10';
    if ($isDisabled) {
        $inputClass .= ' input-disabled bg-base-200 cursor-not-allowed';
    }
    if ($readonly) {
        $inputClass .= ' input-disabled bg-base-200 cursor-not-allowed';
    }
@endphp

<div class="form-control w-full {{ $class }}"
    x-data="{
        open: false,
        search: '{{ $initialLabel }}',
        highlighted: -1,
        items: {{ $items->toJson() }},
        disabled: {{ $isDisabled ? 'true' : 'false' }},
        readonly: {{ $readonly ? 'true' : 'false' }},

        get filtered() {
            if (!this.search) return this.items;
            return this.items.filter(i => i.label && i.label.toLowerCase().includes(this.search.toLowerCase()));
        },

        select(item) {
            if (this.disabled || this.readonly) return;

            $refs.hidden.value = item.id;
            $refs.hidden.dispatchEvent(new Event('input', { bubbles: true }));
            $refs.hidden.dispatchEvent(new Event('change', { bubbles: true }));
            this.search = item.label;
            this.open = false;
        },

        clear() {
            if (this.disabled || this.readonly) return;

            $refs.hidden.value = '';
            $refs.hidden.dispatchEvent(new Event('input', { bubbles: true }));
            $refs.hidden.dispatchEvent(new Event('change', { bubbles: true }));
            this.search = '';
            this.open = false;
        },

        highlightNext() {
            if (this.disabled || this.readonly) return;
            if (this.highlighted < this.filtered.length - 1) this.highlighted++;
        },

        highlightPrev() {
            if (this.disabled || this.readonly) return;
            if (this.highlighted > 0) this.highlighted--;
        },

        chooseHighlighted() {
            if (this.disabled || this.readonly) return;
            if (this.filtered.length && this.highlighted > -1) {
                this.select(this.filtered[this.highlighted]);
            }
        },

        syncFromHidden() {
            const hiddenVal = $refs.hidden?.value;
            if (hiddenVal) {
                const found = this.items.find(i => String(i.id) === String(hiddenVal));
                this.search = found ? found.label : '';
            } else {
                this.search = '';
            }
        },

        handleFocus() {
            if (!this.disabled && !this.readonly) {
                this.open = true;
            }
        },

        handleInput() {
            if (!this.disabled && !this.readonly) {
                this.open = true;
                this.highlighted = -1;
            }
        }
    }"
    x-init="
        // Sync initially from hidden input
        $nextTick(() => syncFromHidden());

        // Listen to Livewire updates
        Livewire.on('{{ $name }}Updated', () => {
            $nextTick(() => syncFromHidden());
        });
    "
    @keydown.escape="if(!disabled && !readonly) open=false"
    @click.away="if(!disabled && !readonly) open = false"
    class="relative"
>

    @if($label)
    <label class="label mb-1">
        <div class="flex items-center gap-1">
            <span class="label-text {{ $isDisabled ? 'opacity-70' : '' }}">
                {{ $label }}
                @if ($required)
                    <span class="text-red-500 text-sm font-normal">*</span>
                @endif
            </span>
        </div>
    </label>
    @endif

    <div class="relative">
        <input
            x-model="search"
            @focus="handleFocus()"
            @input="handleInput()"
            @keydown.arrow-down.prevent="highlightNext()"
            @keydown.arrow-up.prevent="highlightPrev()"
            @keydown.enter.prevent="chooseHighlighted()"
            type="text"
            placeholder="{{ $placeholder }}"
            :disabled="disabled || readonly"
            :readonly="readonly"
            class="{{ $inputClass }}"
            :class="{ 'cursor-not-allowed opacity-70': disabled || readonly }"
            autocomplete="off"
        />

        {{-- clear button --}}
        <button
            type="button"
            x-show="search && !disabled && !readonly"
            x-cloak
            @click="clear()"
            class="absolute right-8 top-1/2 -translate-y-1/2 btn btn-ghost btn-xs"
        >
            &times;
        </button>

        {{-- Hidden input bound to Livewire model --}}
        <input
            type="hidden"
            x-ref="hidden"
            @if($live)
                wire:model.live="{{ $name }}"
            @else
                wire:model="{{ $name }}"
            @endif
            @if($isDisabled)
                disabled
            @endif
        />
    </div>

    <div
        x-show="open && !disabled && !readonly"
        x-cloak
        class="absolute z-50 mt-1 max-h-56 w-full overflow-auto bg-base-100 border rounded-lg shadow-lg"
    >
        <template x-if="filtered.length">
            <ul class="menu w-full p-2">
                <template x-for="(item, idx) in filtered" :key="item.id">
                    <li>
                        <button
                            type="button"
                            class="w-full text-left px-3 py-2 hover:bg-base-200"
                            :class="highlighted === idx ? 'bg-base-200' : ''"
                            @mouseenter="if(!disabled && !readonly) highlighted = idx"
                            @mouseleave="if(!disabled && !readonly) highlighted = -1"
                            @click="select(item)"
                            :disabled="disabled"
                        >
                            <span x-text="item.label"></span>
                        </button>
                    </li>
                </template>
            </ul>
        </template>

        <template x-if="!filtered.length">
            <div class="p-4 text-center text-sm text-gray-500">
                <div>{{ $noResults }}</div>
                @if($actionLabel && $actionEvent && !$isDisabled)
                    <div class="mt-3">
                        <button
                            wire:click="{{ $actionEvent }}"
                            type="button"
                            class="btn btn-primary btn-sm"
                            @if($isDisabled) disabled @endif
                        >
                            {{ $actionLabel }}
                        </button>
                    </div>
                @endif
            </div>
        </template>
    </div>

    @error($name)
        <p class="text-xs mt-1 text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
