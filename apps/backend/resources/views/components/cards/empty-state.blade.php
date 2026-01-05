@props([
    'title' => 'Belum ada item',
    'subtitle' => null,
    'description' => null,
    'actionLabel' => null,
    'actionEvent' => null, // expects a string like "$dispatch('showCreateForm')"
    'image' => null,
    'class' => '',
])

<div class="w-full p-8 flex flex-col items-center justify-center text-center {{ $class }}">
    @if($image)
        <img src="{{ $image }}" alt="{{ $title }}" class="w-40 h-40 object-cover rounded-lg mb-4" />
    @else
        <svg class="w-20 h-20 text-gray-300 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M8 7V5a4 4 0 118 0v2" />
        </svg>
    @endif

    <h3 class="text-lg font-semibold">{{ $title }}</h3>

    @if($subtitle)
        <p class="text-sm opacity-70 mt-1">{{ $subtitle }}</p>
    @endif

    @if($description)
        <p class="text-sm opacity-60 mt-2 max-w-md">{{ $description }}</p>
    @endif

    @if($actionLabel && $actionEvent)
        <div class="mt-4">
            <button class="btn btn-primary btn-sm" wire:click="{{ $actionEvent }}">
                {{ $actionLabel }}
            </button>
        </div>
    @endif
</div>
