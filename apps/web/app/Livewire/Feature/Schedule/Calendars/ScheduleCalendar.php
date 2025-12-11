<?php

namespace App\Livewire\Feature\Schedule\Calendars;

use App\Models\Student;
use Asantibanez\LivewireCalendar\LivewireCalendar;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class ScheduleCalendar extends LivewireCalendar
{
    public function events(): Collection
    {
        return Student::query()
            ->whereDate('created_at', '>=', $this->gridStartsAt)
            ->whereDate('created_at', '<=', $this->gridEndsAt)
            ->get()
            ->map(function (Student $model) {
                return [
                    'id' => $model->id,
                    'title' => $model->nim,
                    'description' => $model->generation,
                    'time' => $model->nim,
                    'date' => $model->created_at,
                ];
            });
    }
}
