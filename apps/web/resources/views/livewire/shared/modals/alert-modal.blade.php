<div>
    <!-- Daisy UI Modal -->
    <div class="modal @if($show) modal-open @endif">
        <div class="modal-box {{ $size === 'sm' ? 'max-w-sm' : ($size === 'lg' ? 'max-w-lg' : ($size === 'xl' ? 'max-w-xl' : 'max-w-md')) }}">
            <!-- Close Button -->
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" wire:click="closeModal">✕</button>
            </form>

            <!-- Icon Section -->
            <div class="flex justify-center mb-4">
                @switch($type)
                    @case('success')
                        <div class="rounded-full bg-success/20 p-3">
                            <svg class="w-8 h-8 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        @break
                    @case('error')
                        <div class="rounded-full bg-error/20 p-3">
                            <svg class="w-8 h-8 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        @break
                    @case('warning')
                        <div class="rounded-full bg-warning/20 p-3">
                            <svg class="w-8 h-8 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        @break
                    @case('info')
                        <div class="rounded-full bg-info/20 p-3">
                            <svg class="w-8 h-8 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        @break
                @endswitch
            </div>

            <!-- Content Section -->
            <div class="text-center space-y-3">
                <h3 class="text-lg font-bold text-gray-900">{{ $title }}</h3>
                <p class="text-gray-600 leading-relaxed">{{ $message }}</p>
            </div>

            <!-- Actions Section -->
            <div class="modal-action justify-center">
                @if($showCancelButton)
                    <button type="button" class="btn btn-outline" wire:click="cancel">
                        {{ $cancelText }}
                    </button>
                @endif

                @if($actionUrl)
                    <a href="{{ $actionUrl }}" class="btn btn-{{ $type }}">
                        {{ $actionText }}
                    </a>
                @else
                    <button type="button" class="btn btn-{{ $type }}" wire:click="performAction">
                        {{ $actionText }}
                    </button>
                @endif
            </div>
        </div>

        <!-- Backdrop - Click outside to close -->
        <form method="dialog" class="modal-backdrop">
            <button wire:click="closeModal">close</button>
        </form>
    </div>
</div>
