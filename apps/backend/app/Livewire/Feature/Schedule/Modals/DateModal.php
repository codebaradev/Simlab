<?php

namespace App\Livewire\Feature\Schedule\Modals;

use Livewire\Component;

class DateModal extends Component
{
    public ?string $date = null;
    public array $events = [];
    public bool $show = false;

    protected $listeners = [
        'openDateModal' => 'openModal'
    ];

    public function openModal(string $date, $events = null)
    {
        $this->date = $date;

        if (is_array($events) && count($events)) {
            $this->events = $events;
        } else {
            // fallback kosong — jika butuh query DB, lakukan di sini
            $this->events = [];
        }

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
        return view('livewire.feature.schedule.modals.date-modal');
    }
}
