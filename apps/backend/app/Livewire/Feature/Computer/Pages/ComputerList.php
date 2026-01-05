<?php

namespace App\Livewire\Feature\Computer\Pages;

use App\Services\RoomService;
use Livewire\Component;
use App\Models\Room;

class ComputerList extends Component
{
    protected RoomService $rService;

    public Room $room;

    public function boot(RoomService $rService)
    {
        $this->rService = $rService;
    }

    public function mount(int $roomId)
    {
        $this->room = $this->rService->findById($roomId);
    }

    public function render()
    {
        return view('livewire.feature.computer.pages.computer-list');
    }
}
