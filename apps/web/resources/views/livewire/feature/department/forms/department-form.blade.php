<div>
    <form wire:submit="save">
        <div class="space-y-4">
            <!-- Code Input -->
            <x-form.input name="code" label="Kode Jurusan"/>
            <x-form.input name="name" label="Nama Jurusan"/>
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
