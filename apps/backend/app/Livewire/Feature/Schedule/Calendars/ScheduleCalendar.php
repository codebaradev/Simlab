<?php

namespace App\Livewire\Feature\Schedule\Calendars;

use App\Enums\Schedule\StatusEnum;
use App\Models\Schedule;
use Asantibanez\LivewireCalendar\LivewireCalendar;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleCalendar extends LivewireCalendar
{
    protected $listeners = [
        'refresh-calendar' => '$refresh',
        'previousMonth',
        'nextMonth',
        'goToMonth',
        'goToToday'
    ];

    public function events(): Collection
    {
        // ...existing code...
        $start = $this->gridStartsAt instanceof Carbon ? $this->gridStartsAt->toDateString() : Carbon::parse($this->gridStartsAt)->toDateString();
        $end   = $this->gridEndsAt instanceof Carbon ? $this->gridEndsAt->toDateString() : Carbon::parse($this->gridEndsAt)->toDateString();

        return Schedule::query()
            ->whereNot('status', StatusEnum::REJECTED)
            ->whereDate('created_at', '<=', $end)
            ->whereDate('created_at', '<=', $end)
            ->get()
            ->map(callback: function (Schedule $schedule) {
                return [
                    'id' => $schedule->id,
                    'status' => $schedule->status,
                    'title' => $schedule->course ? $schedule->course->name : $schedule->schedule_request->category->label(),
                    'lecturerCode' => $schedule->schedule_request->lecturer ? $schedule->schedule_request->lecturer->code : null,
                    'class' => $schedule->course ? $schedule->course->academic_classes[0]->code : null,
                    'rooms' => $schedule->rooms->pluck('name')->implode(' | '),
                    'time' => $schedule->time->label(),
                    'date' => optional($schedule->start_date)->toDateString(),
                ];
            });
    }

    public function onDayClick($year, $month, $day)
    {
        $date = Carbon::create($year, $month, $day)->toDateString();

        $eventsForDate = Schedule::whereDate('created_at', $date)
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'title' => $m->nim,
                    'description' => $m->generation,
                    'time' => optional($m->created_at)->format('H:i') ?? null,
                    'start_at' => optional($m->start_datetime)->format('Y-m-d H:i') ?? null,
                    'end_at' => optional($m->end_datetime)->format('Y-m-d H:i') ?? null,
                    'date' => optional($m->created_at)->toDateString(),
                ];
            })->values()->all();

        // emit ke client/modal
        $this->dispatch('openDateModal', $date, $eventsForDate);
    }

    public function onAddDayClick($year, $month, $day)
    {
        $this->dispatch('showRequestFormModal', $year, $month, $day);
    }

    // when gridStartsAt changes, notify parent/page so it can update month/year display
    public function updatedGridStartsAt($value)
    {
        $dt = $value instanceof Carbon ? $value : Carbon::parse($value);

        // emitUp supaya parent Livewire (ScheduleIndexPage) menerima update
        $this->dispatch('calendarMonthChanged', [
            'month' => (int) $dt->format('n'),
            'year'  => (int) $dt->format('Y'),
            'label' => $dt->format('F Y'),
        ]);
    }

    public function goToMonth($year, $month)
    {
        $date = Carbon::create($year, $month, 1);

        // set startsAt sebagai first-of-month (dipakai view untuk label)
        $this->startsAt = $date->copy()->startOfMonth();

        // set grid range (Carbon instances)
        $this->gridStartsAt = $this->startsAt->copy()->startOfWeek();
        $this->gridEndsAt   = $this->startsAt->copy()->endOfMonth()->endOfWeek();

        $this->updatedGridStartsAt($this->gridStartsAt);
    }

    public function previousMonth()
    {
        // gunakan startsAt jika tersedia, fallback ke gridStartsAt
        $base = $this->startsAt instanceof Carbon ? $this->startsAt : ($this->gridStartsAt instanceof Carbon ? $this->gridStartsAt : Carbon::now());

        $firstOfPrev = $base->copy()->subMonth()->startOfMonth();

        $this->startsAt    = $firstOfPrev->copy()->startOfMonth();
        $this->gridStartsAt = $this->startsAt->copy()->startOfWeek();
        $this->gridEndsAt   = $this->startsAt->copy()->endOfMonth()->endOfWeek();

        $this->updatedGridStartsAt($this->gridStartsAt);
    }

    public function nextMonth()
    {
        $base = $this->startsAt instanceof Carbon ? $this->startsAt : ($this->gridStartsAt instanceof Carbon ? $this->gridStartsAt : Carbon::now());

        $firstOfNext = $base->copy()->addMonth()->startOfMonth();

        $this->startsAt    = $firstOfNext->copy()->startOfMonth();
        $this->gridStartsAt = $this->startsAt->copy()->startOfWeek();
        $this->gridEndsAt   = $this->startsAt->copy()->endOfMonth()->endOfWeek();

        $this->updatedGridStartsAt($this->gridStartsAt);
    }

    public function goToToday()
    {
        $today = Carbon::now();
        $firstOfMonth = $today->copy()->startOfMonth();

        $this->startsAt    = $firstOfMonth->copy()->startOfMonth();
        $this->gridStartsAt = $this->startsAt->copy()->startOfWeek();
        $this->gridEndsAt   = $this->startsAt->copy()->endOfMonth()->endOfWeek();

        $this->updatedGridStartsAt($this->gridStartsAt);
    }

    public function requestSchedule( )
    {

    }
}
