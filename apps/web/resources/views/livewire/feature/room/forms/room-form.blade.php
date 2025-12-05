@php

use App\Enums\RoomStatusEnum;

@endphp

<div class="card">
    <div class="card-body p-0">
        <!-- Header -->
        <div class="mb-4">
            <h2 class="card-title text-2xl font-bold">
                {{ $room ? 'Edit Ruangan' : 'Tambah Ruangan' }}
            </h2>
        </div>

        <form wire:submit="save" class="space-y-6">
            <!-- Section 1: Data Pribadi -->
            <div>
                <div class="grid gap-6">
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                        <!-- Nama Lengkap -->
                        <x-form.input
                            name="name"
                            label="Nama Ruangan"
                            placeholder="Nama Ruangan"
                            :required="true"
                        />

                        <!-- Kode -->
                        <x-form.input
                            name="code"
                            label="Kode"
                            placeholder="20*"
                            :required="true"
                        />

                        <!-- Status -->
                        <x-form.select
                            name="status"
                            label="Status"
                            :options="RoomStatusEnum::toArray()"
                            optionValue="value"
                            optionLabel="label"
                            placeholder="Pilih Status"
                            :required="true"
                        />
                    </div>
                </div>
            </div>

            <!-- Error Global -->
            @error('general')
                <div class="alert alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $message }}</span>
                </div>
            @enderror

            <!-- Form Actions -->
            <div class="card-actions justify-start pt-6 border-t">
                <a href="/ruangan" wire:navigate>
                    <x-button type="button" variant="cancel">
                        Kembali
                    </x-button>
                </a>

                @if ($room)
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
