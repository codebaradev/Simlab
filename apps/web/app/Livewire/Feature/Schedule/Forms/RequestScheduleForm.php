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
        $this->repeat_count = 1;
        $this->time = TimeEnum::TIME_1->value;
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
        $this->occurrences = [];
    }

    public function updatedRepeatCount()
    {
        $this->generateOccurrences();
    }

    public function updatedRoomIds()
    {
        $this->generateOccurrences();
    }

    public function updatedStartDate()
    {
        $this->generateOccurrences();
    }

    public function updatedTime()
    {
        $this->generateOccurrences();
    }

    public function generateOccurrences(): void
    {
        $this->occurrences = [];

        if (empty($this->start_date) || empty($this->time) || empty($this->repeat_count) || empty($this->room_ids)) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'Isi tanggal, waktu mulai, waktu selesai dan repeat terlebih dahulu.']);
            return;
        }

        try {
            $startBase = Carbon::parse("{$this->start_date}");
        } catch (\Throwable $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Format tanggal/waktu tidak valid.']);
            return;
        }

        $repeat = max(1, (int) $this->repeat_count);

        $roomMap = collect($this->rooms)
            ->keyBy('id')
            ->map(fn ($room) => $room->name);

        $roomNames = collect($this->room_ids)
            ->map(fn ($id) => $roomMap[$id] ?? '-')
            ->values()
            ->toArray();

        for ($i = 0; $i < $repeat; $i++) {
            $startIter = $startBase->copy()->addWeeks($i);

            $this->occurrences[] = [
                'room_ids' => $this->room_ids,
                'room_names' => $roomNames,
                'start_date' => $startIter->toDateString(),
                'time' => $this->time,
            ];
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

                    $schedules[] = [
                        'room_ids' => $this->room_ids,
                        'course_id' => $this->course_id ?? null,
                        'start_date' => $occ['start_date'],
                        'time' => $occ['time'],
                        'status' => StatusEnum::PENDING,
                        'is_open' => false,
                        'building' => BuildingEnum::CAMPUS_1,
                        'information' => $this->information ?? null,
                    ];
                }

                $payload = array_merge($srData, ['schedules' => $schedules]);

                $this->srService->createWithSchedules($payload);
            } else {
                // fallback: no explicit occurrences, use grouped rooms + start/end + optional time
                $payload = array_merge($srData, [
                    'course_id' => $this->course_id,
                    'room_ids' => $this->room_ids,
                    'start_date' => $this->start_date,
                    'time' => $this->time ?? null,
                    'repeat_count' => $srData['repeat_count'],
                ]);


                $this->srService->createWithSchedules($payload);
            }

            $this->dispatch('closeRequestFormModal');
            $this->dispatch('refresh-calendar');

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
