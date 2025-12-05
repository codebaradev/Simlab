<?php

namespace App\Livewire\Feature\Room\Pages;

use App\Models\Room;
use App\Services\RoomService;
use Livewire\Component;

class RoomFormPage extends Component
{
    protected RoomService $rService;
    // protected $studentId;

    public ?Room $room = null;

    public function boot(RoomService $rService)
    {
        $this->rService = $rService;
    }

    public function mount($roomId = null)
    {
        if ($roomId) {
            $this->room = $this->rService->findById($roomId, with: []);
        }
    }

    public function render()
    {
        return view('livewire.feature.room.pages.room-form-page');
    }
}
