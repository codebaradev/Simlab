<!-- resources/views/components/card.blade.php -->
@props([
    'title' => 'Judul Card',
    'subtitle' => null,
    'image' => null,
    'actions' => false,
])

<div class="card bg-base-100 shadow-md hover:shadow-lg transition rounded-xl overflow-hidden">
    @if($image)
        <figure>
            <img src="{{ $image }}" alt="Card Image" class="w-full h-40 object-cover" />
        </figure>
    @endif

    <div class="card-body p-4">
        <h2 class="card-title text-lg">{{ $title }}</h2>

        @if($subtitle)
            <p class="opacity-70 text-sm">{{ $subtitle }}</p>
        @endif

        <div class="mt-3 text-sm">
            {{ $slot }}
        </div>

        @if($actions)
            <div class="card-actions justify-end mt-4">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>


<!-- New: Card List Item Component for Matkul (table-like usage) -->
{{--
Usage:
<x-card-matkul :name="$matkul->name" :code="$matkul->code" :sks="$matkul->sks" :semester="$matkul->semester">
    <x-slot:actions>
        <button class="btn btn-sm btn-primary">Detail</button>
    </x-slot:actions>
</x-card-matkul>
--}}

<!-- Example usage: -->
{{--
<x-card
    title="Nama Mahasiswa"
    subtitle="12345678 • Informatika"
    image="https://placehold.co/400x250"
>
    <p>Ini adalah deskripsi singkat mahasiswa atau informasi lain.</p>

    <x-slot:actions>
        <a href="#" class="btn btn-primary btn-sm">Detail</a>
        <a href="#" class="btn btn-outline btn-sm">Edit</a>
    </x-slot:actions>
</x-card>
--}}
