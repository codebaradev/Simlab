<?php

namespace App\Livewire\Feature\Course\Tables;

use App\Models\Schedule;
use App\Models\Attendance;
use Livewire\Component;
use Livewire\WithPagination;

class CourseAttendanceTable extends Component
{
    use WithPagination;

    public $courseId;
    public $selectedIndex = 0; // index of selected schedule
    public $perPage = 10;

    protected $queryString = [
        'selectedIndex' => ['except' => 0],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'refresh-attendance' => '$refresh'
    ];

    public function mount($courseId)
    {
        $this->courseId = $courseId;
    }

    public function updatingSelectedIndex()
    {
        $this->resetPage();
    }

    public function getSchedulesProperty()
    {
        return Schedule::where('course_id', $this->courseId)
            ->orderBy('start_date')
            ->get();
    }

    public function getSelectedScheduleProperty()
    {
        return $this->schedules->get($this->selectedIndex);
    }

    public function getAttendancesProperty()
    {
        $schedule = $this->selectedSchedule;
        if (! $schedule) {
            return Attendance::whereRaw('0=1')->paginate($this->perPage);
        }

        return Attendance::with('user')
            ->where('schedule_id', $schedule->id)
            ->orderBy('id')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.feature.course.tables.course-attendance-table', [
            'schedules' => $this->schedules,
            'attendances' => $this->attendances,
            'selectedSchedule' => $this->selectedSchedule,
        ]);
    }
}

