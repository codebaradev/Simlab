<?php

namespace App\Livewire\Feature\Computer\Forms;

use App\Enums\Computer\CategoryEnum;
use App\Enums\Computer\DisplayResolutionEnum;
use App\Enums\Computer\OsEnum;
use App\Enums\Computer\RamTypeEnum;
use App\Enums\Computer\StorageTypeEnum;
use App\Services\ComputerService;
use Livewire\Component;
use App\Services\RoomService;
use App\Traits\Livewire\WithAlertModal;
use DB;
use Illuminate\Validation\Rule;
class ComputerForm extends Component
{
    use WithAlertModal;

    protected RoomService $rService;
    protected ComputerService $cpService;
    public $room;
    public $computer;
    public bool $isEditing;

    // User input
    public $computer_count;
    public $name;
    public $processor;
    public $gpu;
    public $ram_capacity;
    public $ram_type;
    public $storage_type;
    public $storage_capacity;
    public $display_size;
    public $display_resolution;
    public $display_refresh_rate;
    public $os;
    public $release_year;
    public $category;

    public function boot(RoomService $rService, ComputerService $cpService)
    {
        $this->rService = $rService;
        $this->cpService = $cpService;
    }

    public function mount($room, $computer = null)
    {
        $this->room = $room;
        $this->computer = $computer;
        $this->isEditing = (bool) $this->computer;

        if ($this->computer) {
            $this->computer_count = $this->computer->computer_count;
            $this->name = $this->computer->name;
            $this->processor = $this->computer->processor;
            $this->gpu = $this->computer->gpu;
            $this->release_year = $this->computer->release_year;
            $this->ram_type = $this->computer->ram_type;
            $this->ram_capacity = $this->computer->ram_capacity;
            $this->storage_type = $this->computer->storage_type;
            $this->storage_capacity = $this->computer->storage_capacity;
            $this->display_size = $this->computer->display_size;
            $this->display_resolution = $this->computer->display_resolution;
            $this->display_refresh_rate = $this->computer->display_refresh_rate;
            $this->os = $this->computer->os;
            $this->category = $this->computer->category;
        }
    }

    public function save()
    {
        $computerId = $this->isEditing && $this->computer ? $this->computer->id : null;

        $rules = [
            'name' => ['required', 'string', 'max:100', Rule::unique('computers', 'name')->ignore($computerId)],
            'computer_count' => ['required', 'integer', 'min:1', 'max:200'],
            'category' => ['required', Rule::enum(CategoryEnum::class)],
            'release_year' => ['required', 'integer', 'min:2000', 'max:' . date('Y')],

            // Hardware Specifications
            'processor' => ['required', 'string', 'max:255'],
            'gpu' => ['required', 'string', 'max:255',],

            // Memory
            'ram_capacity' => ['required', 'integer', 'min:1', 'max:512'],
            'ram_type' => ['required', Rule::enum(RamTypeEnum::class)],
            'storage_type' => ['required', Rule::enum(StorageTypeEnum::class)],
            'storage_capacity' => ['required', 'integer', 'min:1', 'max:100000'],

            // Display
            'display_size' => ['required', 'numeric', 'min:10', 'max:50'],
            'display_resolution' => ['required', Rule::enum(DisplayResolutionEnum::class)],
            'display_refresh_rate' => [ 'required','integer','min:30'],

            // Operating System
            'os' => ['required', Rule::enum(OsEnum::class)],
        ];

        $validated = $this->validate($rules);

        try {
            DB::transaction(function () use ($validated) {
                if ($this->computer) {
                    $this->cpService->update($this->computer, $validated);
                } else {
                    $this->cpService->create($this->room->id,  $validated);
                    return $this->redirectRoute('room.computer.index', ['roomId' => $this->room->id], navigate: true);
                }

                return $this->showSuccessAlert('Data Ruangan Berhasil Diupdate');
            });
        } catch (\Exception $e) {
            $this->showErrorAlert("Terjadi kesalahan, silahkan coba lagi!!!");
        }
    }

    public function render()
    {
        $options = [
            'categories' => CategoryEnum::toArray(),
            'displayResolutions' => DisplayResolutionEnum::toArray(),
            'osOptions' => OsEnum::toArray(),
            'ramTypes' => RamTypeEnum::toArray(),
            'storageTypes' => StorageTypeEnum::toArray(),
        ];

        return view('livewire.feature.computer.forms.computer-form', [
            'options' => $options,
        ]);
    }
}
