<?php

namespace App\Livewire\Feature\Schedule\Forms;

use App\Enums\Schedule\BuildingEnum;
use App\Enums\Schedule\TimeEnum;
use App\Enums\ScheduleRequest\CategoryEnum;
use App\Enums\ScheduleRequest\StatusEnum;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Room;
use App\Services\ScheduleRequestService;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RequestScheduleForm extends Component
{
    // ...existing code...

    // form fields
    public $course_id;
    public $lecturer_id;
    public $room_ids = [];
    public $category;
    public $information;
    public $repeat_count = 1;

    public $start_date;
    public $time;

    // lists for selects/cards
    public $courses = [];
    public $lecturers = [];
    public $rooms = [];

    // tab state: 'matakuliah' or 'lainnya'
    public $activeTab = 'matakuliah';

    // generated occurrences array shown in sidebar
    // each item: ['room_id' => int|null, 'start' => 'Y-m-d\TH:i', 'end' => 'Y-m-d\TH:i']
    public $occurrences = [];

    protected $listeners = [
        'changeDateRequestForm' => 'changeDate'
    ];

    protected ScheduleRequestService $srService;

    public function boot(ScheduleRequestService $srService)
    {
        $this->srService = $srService;
    }

    public function mount()
    {
        // load options
        $this->courses = Course::orderBy('name')->get();
        $this->lecturers = Lecturer::orderBy('nip')->get();
        if (method_exists($this->srService, 'getRooms')) {
            $this->rooms = $this->srService->getRooms();
        } else {
            $this->rooms = Room::orderBy('code')->get();
        }

        $now = Carbon::now()->addHour()->startOfHour();
        $this->start_date = $now->toDateString();
        $this->end_time = $now->copy()->addHour()->format('H:i');
        $this->repeat_count = 1;
        $this->occurrences = [];

        $this->occurrences[] = [
            'room_id' => $this->room_ids[0] ?? null,
            'start_date' => $this->start_date,
            'time' => $this->time
        ];
    }

    protected function rules(): array
    {
        // rules vary by active tab
        if ($this->activeTab === 'matakuliah') {
            return [
                'course_id' => ['required', 'exists:courses,id'],
                'lecturer_id' => ['required', 'exists:lecturers,id'],
                'room_ids' => ['required', 'array', 'min:1'],
                'room_ids.*' => ['required', 'exists:rooms,id'],
                'information' => ['nullable', 'string'],
                'repeat_count' => ['nullable', 'integer', 'min:1'],
                'start_date' => ['required', 'date'],
                'time' => ['required', Rule::enum(TimeEnum::class)],
            ];
        }

        // 'lainnya' tab: no course/lecturer required, require purpose/title (category) and datetime + rooms
        return [
            'course_id' => ['required', 'exists:courses,id'],
            'lecturer_id' => ['nullable', 'exists:lecturers,id'],
            'category' => ['required', Rule::enum(CategoryEnum::class)],
            'information' => ['nullable', 'string'],
            'room_ids' => ['required', 'array', 'min:1'],
            'room_ids.*' => ['required', 'exists:rooms,id'],
            'start_date' => ['required', 'date'],
            'time' => ['required', Rule::enum(TimeEnum::class)],
        ];
    }

    public function changeDate($year, $month, $day)
    {
        $date = Carbon::create($year, $month, $day);
        $this->start_date = $date->toDateString();
    }

    public function updatedActiveTab()
    {
        $this->occurrences = [[
            'room_id' => $this->room_ids[0] ?? null,
            'start_date' => $this->start_date,
            'time' => $this->time,
        ]];
    }

    public function updatedRepeatCount()
    {
        $this->generateOccurrences();
    }

    public function updatedStartDate()
    {
        $this->generateOccurrences();
    }

    public function updatedStartTime()
    {
        $this->generateOccurrences();
    }

    public function updatedEndTime()
    {
        $this->generateOccurrences();
    }

    public function updatedRoomIds()
    {
        $this->generateOccurrences();
    }
    // public function updatedActiveTab()
    // {
    //     $this->generateOccurrences();
    // }

    // ...existing code...

    /**
     * Generate occurrences based on start_date + start_time, end_time and repeat_count.
     * Each repeat moves the date +1 week. If multiple rooms selected, generates one occurrence per room per week.
     */
    public function generateOccurrences(): void
    {
        $this->occurrences = [];

        if (empty($this->start_date) || empty($this->time) || empty($this->repeat_count)) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'Isi tanggal, waktu mulai, waktu selesai dan repeat terlebih dahulu.']);
            return;
        }

        try {
            $startBase = Carbon::parse("{$this->start_date} {$this->start_time}");
            $endBase = Carbon::parse("{$this->start_date} {$this->end_time}");
        } catch (\Throwable $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Format tanggal/waktu tidak valid.']);
            return;
        }

        $repeat = max(1, (int) $this->repeat_count);

        for ($i = 0; $i < $repeat; $i++) {
            $startIter = $startBase->copy()->addWeeks($i);
            $endIter = $endBase->copy()->addWeeks($i);

            // if no rooms selected, still create occurrence with null room
            if (empty($this->room_ids)) {
                $this->occurrences[] = [
                    'room_id' => null,
                    'start_date' => $startIter->toDateString(),
                    'time' => $startIter->format('H:i'),
                ];
                continue;
            }

            foreach ($this->room_ids as $roomId) {
                $this->occurrences[] = [
                    'room_id' => $roomId,
                    'start_date' => $startIter->toDateString(),
                    'time' => $this->time,
                ];
            }
        }
    }

    public function save()
    {
        $this->validate();

        try {
            // Build ScheduleRequest payload
            $srData = [
                'user_id' => auth()->id(),
                'lecturer_id' => $this->lecturer_id ?? null,
                'repeat_count' => max(1, (int) $this->repeat_count),
                'status' => StatusEnum::PENDING,
                'category' => $this->category ?? CategoryEnum::COURSE->value,
                'information' => $this->information ?? null,
            ];

            // If there are explicit occurrences, convert them into schedules payload
            if (!empty($this->occurrences)) {
                $schedules = [];
                foreach ($this->occurrences as $occ) {
                    if (empty($occ['start_date'])) {
                        continue;
                    }

                    $roomIds = [];
                    if (!empty($occ['room_id'])) {
                        // each occurrence currently represents a single room selection
                        $roomIds[] = (int) $occ['room_id'];
                    }

                    // prefer explicit enum `time` from form, otherwise leave null and include start/end as fallback
                    $timeVal = $occ['time'] ?? $this->time ?? null;

                    $schedules[] = [
                        'room_ids' => $roomIds,
                        'course_id' => $this->course_id ?? null,
                        'start_date' => $occ['start_date'],
                        'time' => $timeVal,
                        'status' => StatusEnum::PENDING,
                        'is_open' => false,
                        'building' => BuildingEnum::CAMPUS_1,
                        'information' => $this->information ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $this->srService->createWithSchedules(array_merge($srData, ['schedules' => $schedules]));
            } else {
                // fallback: no explicit occurrences, use grouped rooms + start/end + optional time
                $groupedRoomIds = array_values(array_unique(array_filter($this->room_ids)));
                $payload = array_merge($srData, [
                    'course_id' => $this->course_id,
                    'room_ids' => $groupedRoomIds,
                    'start_date' => $this->start_date,
                    'time' => $this->time ?? null,
                    'repeat_count' => $srData['repeat_count'],
                ]);

                $this->srService->createWithSchedules($payload);
            }
            // } else {
            //     // fallback: use createWithSchedules (it will split into date/time)
            //     $payload = [
            //         'user_id' => auth()->id(),
            //         'lecturer_id' => $this->lecturer_id,
            //         'course_id' => $this->course_id,
            //         'room_ids' => $this->room_ids,
            //         'start_date' => $this->start_date,
            //         'start_time' => $this->start_time,
            //         'end_time' => $this->end_time,
            //         'repeat_count' => $this->repeat_count ?? 1,
            //         'category' => $this->category,
            //         'information' => $this->information,
            //         'status' => StatusEnum::PENDING,
            //         'is_open' => false,
            //     ];

            //     $this->srService->createWithSchedules($payload);
            // }

            $this->dispatch('closeRequestFormModal');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Request jadwal berhasil dikirim.']);

            $this->reset(['course_id','lecturer_id','room_ids','category','information','repeat_count','occurrences']);
            $this->mount();

            return true;
        } catch (\Throwable $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal menyimpan request jadwal.']);
            throw $e;
        }
    }

    public function render()
    {
        $options = [
            'categories' => CategoryEnum::toArrayExclude([CategoryEnum::COURSE->value]),
            'times' => TimeEnum::toArray(),
        ];

        return view('livewire.feature.schedule.forms.request-schedule-form', [
            'courses' => $this->courses,
            'lecturers' => $this->lecturers,
            'rooms' => $this->rooms,
            'options' => $options
        ]);
    }
}
