<?php

namespace App\Livewire\Feature\Course\Tables;

use App\Enums\Attendance\StatusEnum;
use App\Enums\Schedule\TimeEnum;
use App\Models\AttendanceMonitoring;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\User;
use App\Traits\Livewire\WithAlertModal;
use DB;
use Exception;
use Kreait\Firebase\Contract\Database;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CourseAttendanceTable extends Component
{
    use WithPagination, WithAlertModal;
    protected Database $database;


    public $courseId;
    public $selectedIndex = 0; // index of selected schedule
    public $perPage = 10;

    public $data;

    public $topic;
    public $sub_topic;
    public $is_open;

    protected $queryString = [
        'selectedIndex' => ['except' => 0],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'refresh-attendance' => '$refresh',
    ];

    public function boot(Database $database)
    {
        $this->database = $database;
    }

    public function mount($courseId)
    {
        $this->courseId = $courseId;
        $this->is_open = $this->selectedSchedule ? $this->selectedSchedule->is_open : false;
    }

    #[On('fingerprint-scanned')]
    public function handleFingerprint($data)
    {
        try {
            $now = now();
            $presensi = $data['presensi'];
            $presensi_status = $data['presensi_status'];

            if ($data['mode'] != 1) {
                return;
            }

            DB::transaction(function () use ($now, $presensi, $presensi_status) {
                $selectedSch = $this->selectedSchedule;
                $selectedSch->is_open = 1;
                $selectedSch->save();

                $user = User::with(['attendances.schedule'])->where('fp_id', $presensi['fp_id'])->first();

                // dd($user->attendances->toArray());

                $attedance = Attendance::where('user_id', $user->id)
                    ->whereHas('schedule', function ($q) use ($now) {
                        $q->whereDate('start_date', $now->toDateString())
                        ->where('is_open', true)
                        ->where('time', operator: TimeEnum::fromNow($now));
                    })
                    ->with('schedule')
                    ->get()
                    ->first();
                    // ->first(function ($attendance) use ($now) {

                    // });

                $attedance->status = StatusEnum::PRESENT;
                $attedance->save();
            });
        } catch (Exception $e) {

        }


        $this->dispatch('$refresh');
    }



    public function updatingSelectedIndex()
    {
        $this->resetPage();
    }

    public function openAttendance()
    {
        try {

            $this->database
            ->getReference('fingerprint')
            ->update([
                'mode'    => 1,
            ]);
            $this->is_open = true;
        } catch (Exception $e) {
            $this->is_open = false;
        }
    }

    public function closeAttendance()
    {
        try {
            $this->database
            ->getReference('fingerprint')
            ->update([
                'mode'    => 0,
            ]);
            $this->is_open = false;
        } catch (Exception $e) {
            $this->is_open = true;
        }
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

