<?php

namespace App\Livewire\Feature\Computer\Pages;

use App\Models\Computer;
use App\Models\Room;
use App\Services\ComputerService;
use App\Services\RoomService;
use Livewire\Component;

class ComputerFormPage extends Component
{
    protected RoomService $rService;
    protected ComputerService $cpService;
    // protected $studentId;

    public Room $room;
    public ?Computer $computer;

    public function boot(RoomService $rService, ComputerService $cpService)
    {
        $this->rService = $rService;
        $this->cpService = $cpService;
    }

    public function mount($roomId, $computerId = null)
    {
        $this->room = $this->rService->findById($roomId, with: []);

        if ($computerId) {
            $this->computer = $this->cpService->findById($computerId);
        }
    }

    public function render()
    {
        return view('livewire.feature.computer.pages.computer-form-page');
    }
}
