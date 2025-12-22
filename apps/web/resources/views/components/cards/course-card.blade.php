<!-- ...existing code... -->
@props([
    'course',
    'actions' => null,
    'imageUrl' => null,
])

<div {{ $attributes->merge(['class' => 'card bg-base-100 shadow-md hover:shadow-lg transition rounded-xl cursor-pointer overflow-visible']) }}>
    @if($imageUrl)
        <figure>
            <img src="{{ $imageUrl }}" alt="{{ $course->name }}" class="w-full h-40 object-cover" />
        </figure>
    @endif

    @php
        // Prepare lecturer names (fall back to nidn/code if user name not available)
        $lecturers = $course->lecturers->map(function($l){
            return optional(value: $l->user)->name ?? ($l->nidn ?? $l->code ?? '—');
        })->unique()->values()->all();

        $lecturersText = count($lecturers) ? implode(', ', $lecturers) : null;

        // Prepare academic class names (fall back to code or id)
        $classes = $course->academic_classes->map(function($c){
            return $c->code ?? $c->id;
        })->unique()->values()->all();

        $classesText = count($classes) ? implode(', ', $classes) : null;
    @endphp

    <!-- ...existing code... -->
    <div class="card-body p-4">
        {{-- Tahun ajaran + kelas (lebih kecil) --}}
        <div class="flex items-center justify-between gap-3 mb-1">
            @if($classesText)
                <div class="text-xs text-gray-500 truncate max-w-[60%]">
                    {{ $classesText }}
                </div>
            @endif

            <div class="text-xs text-gray-500">
                {{ $course->year ?? '—' }}
                ({{ $course->semester->label() }})
            </div>
        </div>

        <h2 class="card-title text-lg line-clamp-2">{{ $course->name }}</h2>

        <div class="space-y-1 text-sm opacity-70">
            @if($lecturersText)
                <p class="flex items-start gap-2">
                    <span class="font-semibold w-20">Dosen</span>
                    <span class="truncate">{{ $lecturersText }}</span>
                </p>
            @endif

            {{-- SKS & Semester --}}
            <p class="flex items-start gap-2">
                <span class="font-semibold w-20">SKS</span>
                <span>{{ $course->sks ?? '—' }}</span>
            </p>
        </div>

        @if($actions)
            <div class="card-actions justify-end mt-4 gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>
<!-- ...existing code... -->
</div>
<!-- ...existing code... -->
