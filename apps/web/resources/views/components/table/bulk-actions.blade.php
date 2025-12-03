@props([
    'selected' => [],
    'itemName' => 'item',
    'deleteAction' => 'deleteSelected',
    'deleteConfirm' => null,
    'cancelAction' => null,
    'class' => '',
])

@if(count($selected) > 0)
    <div class="p-3 border-t border-gray-100 bg-warning/10 {{ $class }}">
        <div class="flex items-center gap-3">
            <span class="text-sm text-warning">
                {{ count($selected) }} {{ $itemName }} dipilih
            </span>
            <button
                type="button"
                class="btn btn-error btn-sm"
                wire:click="{{ $deleteAction }}"
                @if($deleteConfirm) wire:confirm="{{ $deleteConfirm }}" @endif
            >
                Hapus
            </button>
            <button
                type="button"
                wire:click="{{ $cancelAction ?? '$set(\'selected\', [])' }}"
                class="btn btn-ghost btn-sm"
            >
                Batal
            </button>
        </div>
    </div>
@endif

