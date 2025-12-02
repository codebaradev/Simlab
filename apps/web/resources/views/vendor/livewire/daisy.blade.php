{{-- @if ($paginator->hasPages()) --}}
    <div class="flex sticky bottom-0 z-20 rounded-b-lg flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-gray-200 bg-white">
        {{-- Page Info --}}
        <div class="flex items-center gap-4">
            <div class="text-sm text-gray-600">
                Menampilkan
                <span class="font-semibold text-primary">{{ $paginator->firstItem() ?: 0 }}</span>
                -
                <span class="font-semibold text-primary">{{ $paginator->lastItem() ?: 0 }}</span>
                dari
                <span class="font-semibold text-primary">{{ $paginator->total() }}</span>
                data
            </div>
        </div>

        {{-- Per Page Selector --}}
        @if(property_exists($this, 'perPage'))
            @php
                $perPageOptions = method_exists($this, 'getPerPageOptions') ? $this->getPerPageOptions() : [10, 25, 50, 100];
            @endphp
            <div class="flex items-center gap-2">
                <select wire:model.live="perPage" class="select select-bordered select-sm w-20">
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Pagination Controls --}}
        <div class="join">
            {{-- Previous Button --}}
            @if ($paginator->onFirstPage())
                <button class="join-item btn btn-sm bg-transparent rounded-full border-0 hover:bg-primary/10" disabled>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled" class="join-item btn btn-sm bg-transparent rounded-full border-0 hover:bg-primary/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
            @endif

            {{-- Page Numbers --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <button class="join-item btn btn-sm btn-disabled">
                        {{ $element }}
                    </button>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button class="join-item btn btn-sm btn-active bg-primary rounded-md text-white" wire:key="paginator-page-{{ $page }}">
                                {{ $page }}
                            </button>
                        @else
                            <button wire:click="gotoPage({{ $page }})" class="join-item btn btn-sm border-0 rounded-md bg-transparent hover:bg-primary/10" wire:key="paginator-page-{{ $page }}">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Button --}}
            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled" class="join-item btn btn-sm bg-transparent rounded-full border-0 hover:bg-primary/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            @else
                <button class="join-item btn btn-sm bg-transparent rounded-full border-0 hover:bg-primary/10" disabled>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            @endif
        </div>
    </div>
{{-- @endif --}}
