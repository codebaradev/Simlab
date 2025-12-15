<div
    @if($eventClickEnabled)
        wire:click.stop="onEventClick('{{ $event['id']  }}')"
    @endif
    class="bg-white rounded-lg border py-2 px-3 shadow-md cursor-pointer">

    @if (isset($event['status']))
        @php
            $status = $event['status']
        @endphp

        <div class="text-xs flex items-center mb-1">
            <div aria-label="status" class="status {{ $status->color() }} mr-1"></div>
            <div>{{ $status->label() }}</div>
        </div>
    @endif

    <div class="text-xs flex justify-between">
        <span>
            {{ $event['class']  ?? ''}}
        </span>

        @if (isset($event['lecturerCode']))
            <span>
                {{ $event['lecturerCode'] }}
            </span>
        @endif
    </div>

    <p class="text-sm font-medium">
        {{ $event['title'] }}
    </p>

    <div class="mt-2 text-xs">
        <p>{{ $event['time'] }}</p>
        <p>{{ $event['rooms'] }}</p>


    </div>
</div>
