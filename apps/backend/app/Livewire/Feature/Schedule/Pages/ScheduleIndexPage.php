<?php

namespace App\Livewire\Feature\Schedule\Pages;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Livewire\Component;

class ScheduleIndexPage extends Component
{
    public User $user;
    public int $currentMonth;
    public int $currentYear;
    public bool $showRequestFormModal = false;

    protected $listeners = [
        'calendarMonthChanged' => 'onCalendarMonthChanged', // dari child emitUp
        'showRequestFormModal',
        'closeRequestFormModal'
    ];

    public function mount()
    {
        $this->user = Auth::user();
        $now = Carbon::now();
        $this->currentMonth = (int) $now->format('n'); // 1..12
        $this->currentYear  = (int) $now->format('Y');
    }

    public function onCalendarMonthChanged(array $payload)
    {
        // payload: ['month' => n, 'year' => YYYY, 'label' => 'April 2025']
        $this->currentMonth = (int) ($payload['month'] ?? $this->currentMonth);
        $this->currentYear  = (int) ($payload['year']  ?? $this->currentYear);
    }

    public function showRequestFormModal($year = null, $month = null, $day = null)
    {
        if ($year && $month && $day) {
            $this->dispatch('changeDateRequestForm', $year, $month, $day);
        }
        $this->showRequestFormModal = true;
    }

    public function closeRequestFormModal(): void
    {
        $this->showRequestFormModal = false;
    }

    public function render()
    {
        return view('livewire.feature.schedule.pages.schedule-index-page');
    }
}
