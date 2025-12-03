@props([
    'colspan' => 1,
    'message' => 'Tidak ada data',
    'actionLabel' => null,
    'actionEvent' => null,
    'class' => '',
])

<tr>
    <td colspan="{{ $colspan }}" class="text-center py-8 text-gray-500 {{ $class }}">
        <div class="flex flex-col items-center justify-center gap-2">
            <span>{{ $message }}</span>
            @if($actionLabel && $actionEvent)
                <button class="btn btn-primary btn-sm mt-2" wire:click="{{ $actionEvent }}">
                    {{ $actionLabel }}
                </button>
            @endif
        </div>
    </td>
</tr>

