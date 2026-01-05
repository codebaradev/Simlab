@php

use App\Enums\UserRoleEnum;
$currentYear = now()->year;

@endphp

<div class="card">
    <div class="card-body p-0">

         <!-- Header -->
        <div class="mb-4">
            <h2 class="card-title text-2xl font-bold">
                {{ $isEditing ? 'Data Matakuliah' : 'Tambah Matakuliah' }}
            </h2>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div>
                <div class="grid lg:grid-cols-2 gap-6">
                    <x-form.search-select
                        name="class_id"
                        class="col-span-2"
                        label="Kelas"
                        :options="$academicClasses"            {{-- collection atau array --}}
                        optionValue="id"
                        optionLabel="code"                     {{-- atau 'name' sesuai kebutuhan --}}
                        placeholder="Cari kelas (kode atau nama)..."
                        actionLabel="Tambah Kelas"
                        actionEvent="showCreateClassForm"      {{-- Livewire method di parent --}}
                        :required="true"
                        :live="false"
                        :readonly="!$canEdit"
                    />

                    <!-- Code Input -->
                    <x-form.input
                        name="name"
                        label="Nama Matakuliah"
                        :required="true"
                        :readonly="!$canEdit"
                    />

                    <x-form.input
                        name="sks"
                        type="number"
                        label="SKS"
                        :required="true"
                        :readonly="!$canEdit"
                    />

                    <x-form.input
                        name="year"
                        placeholder="{{ $currentYear - 1 }}/{{ $currentYear }}"
                        label="Tahun Ajaran"
                        :required="true"
                        :readonly="!$canEdit"
                    />

                    <x-form.select
                        name="semester"
                        label="Semester"
                        :options="$options['semester']"
                        optionValue="value"
                        optionLabel="label"
                        placeholder="Pilih Semester"
                        :required="true"
                        :readonly="!$canEdit"
                    />
                </div>
            </div>

            <!-- Form Actions -->
            <div class="card-actions justify-start pt-6 border-t">
                <a href="/matakuliah" wire:navigate>
                    <x-button type="button" variant="cancel">
                        Kembali
                    </x-button>
                </a>


                @if ($canEdit)
                    @if ($isEditing)
                        <x-button type="submit" leftIcon="check" target="save">
                            Update
                        </x-button>
                    @else
                        <x-button type="submit" leftIcon="add" target="save">
                            Simpan
                        </x-button>
                    @endif
                @endif
            </div>
        </form>
    </div>
</div>

