@php

use App\Enums\User\UserGenderEnum;

@endphp

<div class="card">
    <div class="card-body p-0">

        @if ($lecturer)
            @if ($lecturer->user->fp_id)
                <button wire:click="scanFingerPrint" class="alert alert-info alert-soft w-full cursor-pointer" onclick="document.getElementById('fingerprint_modal').showModal()">
                    <x-icon.info class="size-4"/>
                    <span>Sidik jari sudah terdaftar</span>
                    <span class="btn btn-info btn-md">
                        <span>Ganti</span>
                    </span>
                </button>
            @else
                <button wire:click="scanFingerPrint" class="alert alert-error alert-soft w-full cursor-pointer" onclick="document.getElementById('fingerprint_modal').showModal()">
                    <x-icon.warning class="size-4"/>
                    <span>Sidik jari belum terdaftar silahkan <span class="font-semibold hover:underline">daftarkan</span> sidik jari anda</span>
                    <span class="btn btn-error btn-md">
                        <span>Daftarkan</span>
                    </span>
                </button>
            @endif
        @endif

        <!-- Header -->
        <div class="mb-4">
            <h2 class="card-title text-2xl font-bold">
                {{ $lecturer ? 'Edit Dosen' : 'Tambah Dosen' }}
            </h2>
        </div>

        <form wire:submit="save" class="space-y-6">
            <!-- Section 2: Data Akun -->
            <div>
                <h3 class="text-lg font-semibold mb-1">Data Akun</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Username -->
                    <x-form.input
                        name="username"
                        label="Username"
                        placeholder="Username untuk login"
                        :required="true"
                    />

                    <!-- Email -->
                    <x-form.input
                        name="email"
                        label="Email"
                        type="email"
                        placeholder="email@example.com"
                    />

                    <!-- Section 3: Password (hanya untuk create) -->
                    @if(!$lecturer)
                        <!-- Password -->
                        <x-form.input
                            name="password"
                            label="Password"
                            type="password"
                            placeholder="Minimal 8 karakter"
                            :required="true"
                        />

                        <!-- Confirm Password -->
                        <x-form.input
                            name="password_confirmation"
                            label="Konfirmasi Password"
                            type="password"
                            placeholder="Ulangi password"
                            :required="true"
                        />
                    @endif
                </div>
            </div>

            <!-- Section 1: Data Pribadi -->
            <div>
                <h3 class="text-lg font-semibold mb-1">Data Pribadi</h3>
                <div class="grid gap-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- NIDN -->
                        <x-form.input
                            name="nidn"
                            label="NIDN"
                            placeholder="Masukkan NIDN"
                            :required="true"
                        />
                        <!-- NIP -->
                        <x-form.input
                            name="nip"
                            label="NIP"
                            placeholder="Masukkan NIP"
                            :required="true"
                        />
                        <!-- CODE -->
                        <x-form.input
                            name="code"
                            label="Kode Dosen"
                            placeholder="Masukkan Kode Dosen"
                            :required="true"
                        />

                        <!-- Nama Lengkap -->
                        <x-form.input
                            name="name"
                            label="Nama Lengkap"
                            placeholder="Nama lengkap Dosen"
                            :required="true"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">

                        <!-- Program Studi -->
                        <x-form.select
                            name="sp_id"
                            label="Program Studi"
                            :options="$studyPrograms"
                            optionValue="id"
                            optionLabel="name"
                            placeholder="Pilih Program Studi"
                            :required="true"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nomor Telepon -->
                        <x-form.input
                            name="phone_number"
                            label="Nomor Telepon"
                            placeholder="081234567890"
                        />

                        <!-- Jenis Kelamin -->
                        <x-form.select
                            name="gender"
                            label="Jenis Kelamin"
                            :options="UserGenderEnum::toArray()"
                            optionValue="value"
                            optionLabel="label"
                            placeholder="Pilih Jenis Kelamin"
                        />
                    </div>

                    <!-- Section 6: Alamat -->
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Alamat (membutuhkan komponen textarea baru) -->
                        <x-form.textarea
                            name="address"
                            label="Alamat"
                            placeholder="Alamat lengkap"
                            rows="4"
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
                <a href="/dosen" wire:navigate>
                    <x-button type="button" variant="cancel">
                        Kembali
                    </x-button>
                </a>

                @if ($lecturer)
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
