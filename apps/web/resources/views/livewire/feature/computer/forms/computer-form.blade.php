<div class="card">
    <div class="card-body p-0">
        <!-- Header -->
        <div class="mb-4">
            <h2 class="card-title text-2xl font-bold">
                {{ $computer ? 'Edit Komputer' : 'Tambah Komputer' }}
            </h2>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div>
                <div class="grid gap-8">
                    <!-- Section 1: Informasi Dasar -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="h-6 w-1 bg-primary rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-800">Informasi Dasar</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-form.input
                                name="name"
                                label="Nama Komputer *"
                                placeholder="Nama Komputer"
                                :required="true"
                                wire:model="name"
                            />

                            <x-form.input
                                name="computer_count"
                                label="Jumlah Komputer *"
                                type="number"
                                placeholder="1"
                                :required="true"
                                wire:model="computer_count"
                            />

                            <x-form.select
                                name="category"
                                label="Kategori *"
                                :options="$options['categories']"
                                optionValue="value"
                                optionLabel="label"
                                placeholder="Pilih Kategori"
                                :required="true"
                                wire:model="category"
                            />

                            <x-form.input
                                name="release_year"
                                label="Tahun Rilis"
                                type="number"
                                placeholder="2023"
                                wire:model="release_year"
                                :required="true"
                            />
                        </div>
                    </div>

                    <!-- Section 2: Spesifikasi Hardware -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="h-6 w-1 bg-secondary rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-800">Spesifikasi Hardware</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-form.input
                                name="processor"
                                label="Processor"
                                placeholder="Intel Core i7-12700K"
                                wire:model="processor"
                                :required="true"
                            />

                            <x-form.input
                                name="gpu"
                                label="GPU"
                                placeholder="NVIDIA RTX 4070"
                                wire:model="gpu"
                                :required="true"
                            />
                        </div>
                    </div>

                    <!-- Section 3: Memori (RAM & Storage) -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="h-6 w-1 bg-accent rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-800">Memori</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- RAM Sub-section -->
                            <div class="space-y-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-1 bg-blue-300 rounded-full"></div>
                                    <h4 class="text-md font-medium text-gray-700">RAM</h4>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-form.select
                                        name="ram_type"
                                        label="Tipe RAM"
                                        :options="$options['ramTypes']"
                                        optionValue="value"
                                        optionLabel="label"
                                        placeholder="Pilih Tipe RAM"
                                        wire:model="ram_type"
                                        :required="true"
                                    />

                                    <x-form.input
                                        name="ram_capacity"
                                        label="Kapasitas (GB)"
                                        type="number"
                                        placeholder="16"
                                        wire:model="ram_capacity"
                                        :required="true"
                                    />
                                </div>
                            </div>

                            <!-- Storage Sub-section -->
                            <div class="space-y-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-1 bg-green-300 rounded-full"></div>
                                    <h4 class="text-md font-medium text-gray-700">Storage</h4>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-form.select
                                        name="storage_type"
                                        label="Tipe Storage"
                                        :options="$options['storageTypes']"
                                        optionValue="value"
                                        optionLabel="label"
                                        placeholder="Pilih Tipe Storage"
                                        wire:model="storage_type"
                                        :required="true"
                                    />

                                    <x-form.input
                                        name="storage_capacity"
                                        label="Kapasitas (GB)"
                                        type="number"
                                        placeholder="512"
                                        wire:model="storage_capacity"
                                        :required="true"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Display -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="h-6 w-1 bg-warning rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-800">Display</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-form.input
                                name="display_size"
                                label="Ukuran Layar (inch)"
                                type="float"
                                placeholder="15.6"
                                wire:model="display_size"
                                :required="true"
                            />

                            <x-form.select
                                name="display_resolution"
                                label="Resolusi"
                                :options="$options['displayResolutions']"
                                optionValue="value"
                                optionLabel="label"
                                placeholder="Pilih Resolusi"
                                wire:model="display_resolution"
                                :required="true"
                            />

                            <x-form.input
                                name="display_refresh_rate"
                                label="Refresh Rate (Hz)"
                                type="number"
                                placeholder="144"
                                wire:model="display_refresh_rate"
                                :required="true"
                            />
                        </div>
                    </div>

                    <!-- Section 5: Sistem Operasi -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="h-6 w-1 bg-info rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-800">Sistem Operasi</h3>
                        </div>

                        <div class="">
                            <x-form.select
                                name="os"
                                label="Operating System"
                                :options="$options['osOptions']"
                                optionValue="value"
                                optionLabel="label"
                                placeholder="Pilih OS"
                                wire:model="os"
                                :required="true"
                            />
                        </div>
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
                <a href="/ruangan/{{ $room->id }}/komputer" wire:navigate>
                    <x-button type="button" variant="cancel">
                        Kembali
                    </x-button>
                </a>

                @if ($computer)
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
