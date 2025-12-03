<div>
    <form wire:submit="save">
        <div class="space-y-4">
            <!-- Department Select -->
            <x-form.select
                name="department_id"
                label="Jurusan"
                :options="$departments"
                optionValue="id"
                optionLabel="name"
                placeholder="Pilih Jurusan"
            />

            <!-- Code Input -->
            <x-form.input name="code" label="Kode Program Studi"/>
            <x-form.input name="name" label="Nama Program Studi"/>
        </div>

        <!-- Form Actions -->
        <div class="modal-action">
            <x-button type="button" wire:click="$dispatch('closeFormModal')" variant="cancel">
                Batal
            </x-button>

            @if ($editingId)
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

