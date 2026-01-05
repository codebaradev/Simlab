<div class="card">
    <div class="card-body p-0">

         <!-- Header -->
        <div class="mb-4">
            <h2 class="card-title text-2xl font-bold">
                {{ $isEditing ? 'Edit Prodi' : 'Tambah Prodi' }}
            </h2>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div>
                <div class="space-y-4">
                    <!-- Department Select -->
                    <x-form.select
                        name="department_id"
                        label="Jurusan"
                        :options="$departments"
                        optionValue="id"
                        optionLabel="name"
                        placeholder="Pilih Jurusan"
                        :required="true"
                    />

                    <!-- Code Input -->
                    <x-form.input
                        name="code"
                        label="Kode Program Studi"
                        :required="true"
                    />

                    <x-form.input
                        name="name"
                        label="Nama Program Studi"
                        :required="true"
                    />
                </div>
            </div>

            <!-- Form Actions -->
            <div class="card-actions justify-start pt-6 border-t">
                <a href="/prodi" wire:navigate>
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

