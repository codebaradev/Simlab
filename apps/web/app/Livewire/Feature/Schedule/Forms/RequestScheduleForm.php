<?php

namespace App\Livewire\Feature\Schedule\Forms;

use App\Enums\Schedule\BuildingEnum;
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
    public $start_time;
    public $end_time;

    // lists for selects/cards
    public $courses = [];
    public $lecturers = [];
    public $rooms = [];

    // tab state: 'matakuliah' or 'lainnya'
    public $activeTab = 'matakuliah';

    // generated occurrences array shown in sidebar
    // each item: ['room_id' => int|null, 'start' => 'Y-m-d\TH:i', 'end' => 'Y-m-d\TH:i']
    public $occurrences = [];

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
        $this->start_time = $now->format('H:i');
        $this->end_time = $now->copy()->addHour()->format('H:i');
        $this->repeat_count = 1;
        $this->occurrences = [];

        $this->occurrences[] = [
            'room_id' => $this->room_ids[0] ?? null,
            'start_date' => $this->start_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
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
                'start_time' => ['required', 'date_format:H:i'],
                'end_time' => ['required', 'date_format:H:i', 'after_or_equal:start_time'],
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
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after_or_equal:start_time'],
        ];
    }

    public function updatedActiveTab()
    {
        $this->occurrences = [[
            'room_id' => $this->room_ids[0] ?? null,
            'start_date' => $this->start_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
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
        if (empty($this->start_date) || empty($this->start_time) || empty($this->end_time) || empty($this->repeat_count)) {
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
                    'start_time' => $startIter->format('H:i'),
                    'end_time' => $endIter->format('H:i'),
                ];
                continue;
            }

            foreach ($this->room_ids as $roomId) {
                $this->occurrences[] = [
                    'room_id' => $roomId,
                    'start_date' => $startIter->toDateString(),
                    'start_time' => $startIter->format('H:i'),
                    'end_time' => $endIter->format('H:i'),
                ];
            }
        }
    }

    public function removeOccurrence(int $index): void
    {
        if (isset($this->occurrences[$index])) {
            array_splice($this->occurrences, $index, 1);
        }
    }

    public function addOccurrence(): void
    {
        $date = $this->start_date ?? Carbon::now()->toDateString();
        $start = $this->start_time ?? Carbon::now()->addHour()->format('H:i');
        $end = $this->end_time ?? Carbon::now()->addHour()->addHour()->format('H:i');

        $this->occurrences[] = [
            'room_id' => $this->room_ids[0] ?? null,
            'start_date' => $date,
            'start_time' => $start,
            'end_time' => $end,
        ];
    }

    public function save()
    {
        $this->validate();

        try {
            if (!empty($this->occurrences)) {
                // create ScheduleRequest
                $srData = [
                    'user_id' => auth()->id(),
                    'lecturer_id' => $this->lecturer_id ?? null,
                    'repeat_count' => max(1, (int) $this->repeat_count),
                    'status' => StatusEnum::PENDING,
                    'category' => $this->category ?? CategoryEnum::COURSE->value,
                    'information' => $this->information ?? null,
                ];

                $createdSr = $this->srService->create($srData);

                // build schedules from occurrences (already separate date/time)
                $toCreate = [];
                foreach ($this->occurrences as $occ) {
                    if (empty($occ['start_date']) || empty($occ['start_time']) || empty($occ['end_time'])) {
                        continue;
                    }

                    $toCreate[] = [
                        'room_id' => $occ['room_id'] ?? null,
                        'sr_id' => $createdSr->id,
                        'course_id' => $this->course_id ?? null,
                        'start_date' => $occ['start_date'],
                        'start_time' => strlen($occ['start_time']) > 5 ? $occ['start_time'] : ($occ['start_time'] . ':00'),
                        'end_time' => strlen($occ['end_time']) > 5 ? $occ['end_time'] : ($occ['end_time'] . ':00'),
                        'status' => StatusEnum::PENDING,
                        'is_open' => false,
                        'building' => BuildingEnum::CAMPUS_1,
                        'information' => $this->information ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }


                if (property_exists($this->srService, 'scheduleService') && $this->srService->scheduleService) {
                    $this->srService->scheduleService->createMultiple($toCreate);
                } else {
                    $groupedRoomIds = array_values(array_unique(array_filter(array_column($this->occurrences, 'room_id'))));
                    $this->srService->createWithSchedules([
                        'user_id' => $srData['user_id'],
                        'lecturer_id' => $srData['lecturer_id'],
                        'course_id' => $this->course_id ?? null,
                        'room_ids' => $groupedRoomIds,
                        'start_date' => $this->start_date,
                        'start_time' => $this->start_time,
                        'end_time' => $this->end_time,
                        'repeat_count' => $srData['repeat_count'],
                        'category' => $srData['category'],
                        'information' => $srData['information'],
                    ]);
                }
            } else {
                // fallback: use createWithSchedules (it will split into date/time)
                $payload = [
                    'user_id' => auth()->id(),
                    'lecturer_id' => $this->lecturer_id,
                    'course_id' => $this->course_id,
                    'room_ids' => $this->room_ids,
                    'start_date' => $this->start_date,
                    'start_time' => $this->start_time,
                    'end_time' => $this->end_time,
                    'repeat_count' => $this->repeat_count ?? 1,
                    'category' => $this->category,
                    'information' => $this->information,
                    'status' => StatusEnum::PENDING,
                    'is_open' => false,
                ];

                $this->srService->createWithSchedules($payload);
            }

            if (method_exists($this, 'dispatch')) {
                $this->dispatch('closeRequestFormModal');
            } else {
                $this->emitUp('closeRequestFormModal');
            }

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
            'categories' => CategoryEnum::toArrayExclude([CategoryEnum::COURSE->value])
        ];

        return view('livewire.feature.schedule.forms.request-schedule-form', [
            'courses' => $this->courses,
            'lecturers' => $this->lecturers,
            'rooms' => $this->rooms,
            'options' => $options
        ]);
    }
}
