@props(['notifications', 'showNotification'])

<div class="fixed top-4 right-4 z-50 w-96 space-y-3">
    @foreach ($notifications as $notification)
        <div
            x-data="{
                show: true,
                id: '{{ $notification['id'] }}'
            }"
            x-init="
                setTimeout(() => {
                    show = false;
                    $wire.removeNotification(id);
                }, 5000);
            "
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="transform translate-x-full opacity-0"
            x-transition:enter-end="transform translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="transform translate-x-0 opacity-100"
            x-transition:leave-end="transform translate-x-full opacity-0"
            class="bg-white rounded-lg shadow-lg border-l-4 {{ $notification['status'] === 'success' ? 'border-green-500' : 'border-red-500' }} overflow-hidden"
        >
            <div class="p-4">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            @if($notification['status'] === 'success')
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @endif
                            <h4 class="font-semibold text-gray-900">{{ $notification['name'] }}</h4>
                        </div>

                        <p class="mt-1 text-sm {{ $notification['status'] === 'success' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $notification['message'] }}
                        </p>

                        {{-- <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                            <span>{{ $notification['schedule'] }}</span>
                            <span>{{ $notification['time'] }}</span>
                        </div> --}}
                    </div>

                    <button
                        @click="show = false; $wire.removeNotification(id)"
                        class="ml-4 text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>
