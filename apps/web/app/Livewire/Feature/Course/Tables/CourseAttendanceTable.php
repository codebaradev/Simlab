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
    public $selectedIndex = 0;
    public $perPage = 10;
    public $data;
    public $topic;
    public $sub_topic;
    public $is_open;

    // NEW: Properties for attendance status change
    public $attendanceIdToUpdate = null;
    public $newStatus = null;
    public $showStatusModal = false;
    public $notes = '';

    // Notification
    public $notifications = [];
    public $showNotification = false;

    protected $queryString = [
        'selectedIndex' => ['except' => 0],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'refresh-attendance' => '$refresh',
        'change-attendance-status' => 'openChangeStatusModal', // NEW
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

        if ($presensi_status['fp_id'] == 0) {
            throw new Exception($presensi_status['status']);
        }

        DB::transaction(function () use ($now, $presensi, $presensi_status) {
            $selectedSch = $this->selectedSchedule;
            $selectedSch->is_open = 1;
            $selectedSch->save();

            $user = User::with(['attendances.schedule'])->where('fp_id', $presensi['fp_id'])->first();

            // Cari attendance berdasarkan jadwal yang aktif
            $attedance = Attendance::where('user_id', $user->id)
                ->whereHas('schedule', function ($q) use ($now) {
                    $q->whereDate('start_date', $now->toDateString())
                        ->where('is_open', true)
                        ->where('time', operator: TimeEnum::fromNow($now));
                })
                ->first();

            if (!$attedance) {
                throw new Exception('Kehadiran tidak ditemukan untuk jadwal saat ini.');
            }

            $attedance->status = StatusEnum::PRESENT;
            $attedance->save();

            // Tambahkan notifikasi
            $this->addNotification([
                'id' => uniqid(),
                'name' => $user->name,
                'nim' => $user->nim,
                'status' => 'success',
                'message' => 'Berhasil melakukan presensi',
                'time' => now()->format('H:i:s'),
                'schedule' => $selectedSch->name,
            ]);
        });
    } catch (Exception $e) {
        // Notifikasi error
        $this->addNotification([
            'id' => uniqid(),
            'name' => 'Unknown',
            'nim' => 'Unknown',
            'status' => 'error',
            'message' => 'Gagal: ' . $e->getMessage(),
            'time' => now()->format('H:i:s'),
            'schedule' => 'Unknown',
        ]);
        return;
    }

    $this->dispatch('$refresh');
    }

    // Method untuk menambahkan notifikasi
    public function addNotification($notification)
    {
        $this->notifications[] = $notification;

        // Batasi maksimal 5 notifikasi
        if (count($this->notifications) > 5) {
            array_shift($this->notifications);
        }

        $this->showNotification = true;

        // Auto hide notifikasi setelah 5 detik
        $this->dispatch('show-notification');
    }

    // Method untuk menghapus notifikasi
    public function removeNotification($id)
    {
        $this->notifications = collect($this->notifications)
            ->reject(function ($notification) use ($id) {
                return $notification['id'] === $id;
            })
            ->values()
            ->toArray();

        if (empty($this->notifications)) {
            $this->showNotification = false;
        }
    }

    // NEW: Methods for changing attendance status
    public function openChangeStatusModal($attendanceId)
    {
        $this->attendanceIdToUpdate = $attendanceId;
        $attendance = Attendance::with('user')->find($attendanceId);

        if ($attendance) {
            $this->newStatus = $attendance->status?->value ?? null;
            $this->notes = $attendance->notes ?? '';
            $this->showStatusModal = true;
        }
    }

    public function closeStatusModal()
    {
        $this->reset([
            'attendanceIdToUpdate',
            'newStatus',
            'notes',
            'showStatusModal'
        ]);
    }

    public function updateAttendanceStatus()
    {
        $this->validate([
            'newStatus' => 'required|in:' . implode(',', StatusEnum::values()),
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () {
                $attendance = Attendance::findOrFail($this->attendanceIdToUpdate);

                // Get the old status for logging
                $oldStatus = $attendance->status?->label() ?? 'Belum Hadir';

                // Update attendance
                $attendance->update([
                    'status' => StatusEnum::from($this->newStatus),
                    'notes' => $this->notes ?: null,
                    'updated_by' => auth()->id(), // Track who made the change
                    'updated_at' => now(),
                ]);

                // Log the change
            });

            // Show success message
            $this->dispatch('alert-success',
                message: 'Status kehadiran berhasil diperbarui.',
                description: 'Perubahan telah disimpan.'
            );

            // Refresh data
            $this->dispatch('refresh-attendance');
            $this->closeStatusModal();

        } catch (Exception $e) {
            $this->dispatch('alert-error',
                message: 'Gagal memperbarui status kehadiran.',
                description: $e->getMessage()
            );
        }
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

    public function toggleAttendance()
    {
        if ($this->is_open) {
            $this->closeAttendance();
        } else {
            $this->openAttendance();
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

        return Attendance::with(['user', 'schedule'])
            ->where('schedule_id', $schedule->id)
            ->orderBy('id')
            ->paginate($this->perPage);
    }

    // NEW: Get attendance options for dropdown
    public function getStatusOptionsProperty()
    {
        return collect(StatusEnum::cases())->map(function ($status) {
            return [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
            ];
        });
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

        $schedule->load('attendance_monitoring');
    }

    public function render()
    {
        return view('livewire.feature.course.tables.course-attendance-table', [
            'schedules' => $this->schedules,
            'attendances' => $this->attendances,
            'selectedSchedule' => $this->selectedSchedule,
            'statusOptions' => $this->statusOptions, // NEW
        ]);
    }
}
