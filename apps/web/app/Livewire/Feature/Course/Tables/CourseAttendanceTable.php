<?php

namespace App\Livewire\Feature\Course\Tables;

use App\Models\AttendanceMonitoring;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Traits\Livewire\WithAlertModal;
use Livewire\Component;
use Livewire\WithPagination;

class CourseAttendanceTable extends Component
{
    use WithPagination, WithAlertModal;

    public $courseId;
    public $selectedIndex = 0; // index of selected schedule
    public $perPage = 10;

    public $topic;
    public $sub_topic;

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

    // Monitoring
    public function loadMonitoring()
    {
        $schedule = $this->selectedSchedule;

        if (! $schedule) {
            $this->reset(['topic', 'sub_topic']);
            return;
        }

        $monitoring = $schedule->attendance_monitoring;

        if ($monitoring) {
            $this->topic     = $monitoring->topic;
            $this->sub_topic = $monitoring->sub_topic;
        } else {
            $this->reset(['topic', 'sub_topic']);
        }
    }

    public function updatedSelectedIndex()
    {
        $this->resetPage();
        $this->loadMonitoring();
    }

    public function updatedTopic()
    {
        $this->saveMonitoring();
    }

    public function updatedSubTopic()
    {
        $this->saveMonitoring();
    }

    public function saveMonitoring()
    {
        $this->validate([
            'topic'     => 'nullable|string|max:255',
            'sub_topic' => 'nullable|string|max:255',
        ]);

        $schedule = $this->selectedSchedule;

        if (! $schedule) {
            return;
        }

        AttendanceMonitoring::updateOrCreate(
            ['schedule_id' => $schedule->id],
            [
                'topic'     => $this->topic,
                'sub_topic' => $this->sub_topic,
            ]
        );

        // refresh relasi agar view up-to-date
        $schedule->load('attendance_monitoring');
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

