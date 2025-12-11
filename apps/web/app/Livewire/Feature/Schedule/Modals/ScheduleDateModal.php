<?php

namespace App\Livewire\Feature\Schedule\Modals;

use Livewire\Component;
use App\Models\Student;
use Illuminate\Support\Collection;

class ScheduleDateModal extends Component
{
    public ?string $date = null;
    public $events = [];
    public bool $show = false;

    protected $listeners = [
        'openDateModal' => 'openModal'
    ];

    public function openModal(string $date)
    {
        $this->date = $date;
        // Query events for the date — sesuaikan model / kolom bila perlu
        $this->events = Student::whereDate('created_at', $date)->get();
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
        $this->date = null;
        $this->events = [];
    }

    public function render()
    {
        return view('livewire.feature.schedule.modals.schedule-date-modal');
    }
}
