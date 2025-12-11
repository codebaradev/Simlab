@props([
    'id' => '',
    'actions' => [],
    'dropdownPosition' => 'end',
    'class' => '',
])

<td class="{{ $class }}" @click.stop>
    <div class="dropdown dropdown-bottom dropdown-{{ $dropdownPosition }} ">
        <label tabindex="0" class="btn btn-ghost btn-xs">
            <x-icon.ellipsis class="size-6"/>
        </label>
        <ul tabindex="0" class="dropdown-content z-[50] menu p-2 shadow bg-base-100 rounded-box w-32">
            @foreach($actions as $action)
                <li>
                    <button
                        type="button"
                        wire:click="{{ $action['action'] }}({{ $id }})"
                        @if(isset($action['confirm'])) wire:confirm="{{ $action['confirm'] }}" @endif
                        class="{{ $action['class'] ?? '' }} btn btn-ghost justify-start "
                    >
                        @if(isset($action['icon']))
                            @include('components.icon.' . $action['icon'], ['class' => 'size-4'])
                        @endif
                        {{ $action['label'] }}
                    </button>
                </li>
            @endforeach
        </ul>
    </div>
</td>

