@props([
    'id' => '',
    'actions' => [],
    'dropdownPosition' => 'end', // start | end
    'class' => '',
])

<div class="relative {{ $class }}" @click.stop>
    <div class="dropdown dropdown-bottom dropdown-{{ $dropdownPosition }}">
        <button tabindex="0" class="btn btn-ghost btn-sm p-0">
            <x-icon.ellipsis class="size-6"/>
        </button>

        <ul tabindex="0" class="dropdown-content z-[50] menu p-2 shadow bg-base-100 rounded-box w-40">
            @foreach($actions as $action)
                <li>
                    <button
                        type="button"
                        wire:click="{{ $action['action'] }}({{ $id }})"
                        @if(isset($action['confirm'])) wire:confirm="{{ $action['confirm'] }}" @endif
                        class="{{ $action['class'] ?? '' }} btn btn-ghost justify-start w-full"
                    >
                        @if(isset($action['icon']))
                            @include('components.icon.' . $action['icon'], ['class' => 'size-4 mr-2'])
                        @endif
                        {{ $action['label'] }}
                    </button>
                </li>
            @endforeach
        </ul>
    </div>
</div>
