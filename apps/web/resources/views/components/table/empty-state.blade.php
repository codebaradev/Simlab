@props([
    'colspan' => 1,
    'message' => 'Tidak ada data',
    'actionLabel' => null,
    'actionEvent' => null,
    'class' => '',
    'image' => null,
])

<tr>
    <td colspan="{{ $colspan }}" class="text-center py-8 text-gray-500 {{ $class }}">
        <div class="flex flex-col items-center justify-center gap-2">
            @if($image)
                <img src="{{ $image }}" alt="{{ $title }}" class="w-40 h-40 object-cover rounded-lg mb-4" />
            @else
                <svg class="w-20 h-20 text-gray-300 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M8 7V5a4 4 0 118 0v2" />
                </svg>
            @endif

            <span>{{ $message }}</span>
            @if($actionLabel && $actionEvent)
                <button class="btn btn-primary btn-sm mt-2" wire:click="{{ $actionEvent }}">
                    {{ $actionLabel }}
                </button>
            @endif
        </div>
    </td>
</tr>

