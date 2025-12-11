@php
    $currentYear = now()->year;
@endphp

<div class="card">
    <div class="card-body p-0">

         <!-- Header -->
        <div class="mb-4">
            <h2 class="card-title text-2xl font-bold">
                {{ $isEditing ? 'Edit Kelas' : 'Tambah Kelas' }}
            </h2>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div>
                <div class="grid lg:grid-cols-2 gap-6">
                    <!-- Code Input -->
                    <x-form.input
                        name="code"
                        label="Kode Kelas"
                        :required="true"
                    />

                    <x-form.input
                        name="name"
                        label="Nama Kelas"
                        :required="true"
                    />

                    <!-- Department Select -->
                    <x-form.select
                        name="type"
                        label="Jenis Kelas"
                        :options="$options['type']"
                        optionValue="value"
                        optionLabel="label"
                        placeholder="Pilih Jenis Kelas"
                        :required="true"
                    />

                    <!-- Code Input -->
                    <x-form.input
                        name="generation"
                        type="number"
                        :placeholder="$currentYear"
                        label="Angkatan"
                        :required="true"
                    />

                    <x-form.input
                        name="year"
                        placeholder="{{ $currentYear - 1 }}/{{ $currentYear }}"
                        label="Tahun Ajaran"
                        :required="true"
                    />

                     <!-- Department Select -->
                    <x-form.select
                        name="semester"
                        label="Jenis Kelas"
                        :options="$options['semester']"
                        optionValue="value"
                        optionLabel="label"
                        placeholder="Pilih Semester"
                        :required="true"
                    />
                </div>
            </div>

            <!-- Form Actions -->
            <div class="card-actions justify-start pt-6 border-t">
                <a href="/prodi/{{ $studyProgram->id }}/kelas" wire:navigate>
                    <x-button type="button" wire:click="$dispatch('closeFormModal')" variant="cancel">
                        Kembali
                    </x-button>
                </a>

                @if ($isEditing)
                    <x-button type="submit" leftIcon="check" target="save">
                        Update
                    </x-button>
                @else
                    <x-button type="submit" leftIcon="add" target="save">
                        Simpan
                    </x-button>
                @endif
            </div>
        </form>
    </div>
</div>

