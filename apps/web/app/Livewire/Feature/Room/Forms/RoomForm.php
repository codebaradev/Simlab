<?php

namespace App\Livewire\Feature\Room\Forms;

use App\Enums\RoomStatusEnum;
use App\Services\RoomService;
use App\Traits\Livewire\WithAlertModal;
use DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RoomForm extends Component
{
    use WithAlertModal;

    protected RoomService $rService;
    public $room;
    public bool $isEditing;

    // Main User Attribute
    public $code;
    public $name;
    public $status;

    public function boot(RoomService $rService)
    {
        $this->rService = $rService;
    }

    public function mount($room = null)
    {
        $this->room = $room;
        $this->isEditing = (bool) $this->room;

        if ($this->room) {
            $this->code = $this->room->code;
            $this->name = $this->room->name;
            $this->status = $this->room->status->value;
        } else {
            $this->status = RoomStatusEnum::AVAILABLE->value;
        }
    }

    public function save()
    {
        $roomId = $this->isEditing && $this->room ? $this->room->id : null;

        $rules = [
            'code' => ['required', 'max:255', $roomId ? Rule::unique('rooms','code')->ignore($roomId) : 'unique:rooms,code'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(RoomStatusEnum::values())], // atau gunakan Enum Rule jika prefer
        ];

        $validated = $this->validate($rules);

        try {
            DB::transaction(function () use ($validated) {
                if ($this->room) {
                    $this->rService->update($this->room, $validated);
                } else {
                    $this->rService->create( $validated);
                    return $this->redirectRoute('room.index', navigate: true);
                }

                return $this->showSuccessAlert('Data Ruangan Berhasil Diupdate');
            });
        } catch (\Exception $e) {
            $this->addError('code', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.feature.room.forms.room-form');
    }
}
