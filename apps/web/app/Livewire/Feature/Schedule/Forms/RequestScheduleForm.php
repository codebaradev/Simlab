<?php

namespace App\Livewire\Feature\Schedule\Forms;
use Illuminate\Support\Facades\Http;

use App\Enums\Schedule\BuildingEnum;
use App\Enums\Schedule\TimeEnum;
use App\Enums\ScheduleRequest\CategoryEnum;
use App\Enums\ScheduleRequest\StatusEnum;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Room;
use App\Models\Schedule;
use App\Services\ScheduleRequestService;
use App\Traits\Livewire\WithAlertModal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RequestScheduleForm extends Component
{
    // ...existing code...
    use WithAlertModal;

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

    public $recommendations = [];
    public $isLoadingAi = false;

    protected ScheduleRequestService $srService;

    public function boot(ScheduleRequestService $srService)
    {
        $this->srService = $srService;
    }

    public function updatedCourseId($value)
    {   // Ambil matkul
        $course = Course::find($value);

        if (!$course) {
            $this ->recommendations = [];
            return;
        }

        // Ambil ruangan
        $rooms = Room::all();

        $payload = [
            'matkul' => [
                'course_name' => $course->name,
                'num_students' => (int) $course->academic_classes[0]->students()->count() ?? 30,
                'ram_required_gb' => (float) ($course->ram_required ?? 4),
                'cpu_cores_required' => (int) ($course->cpu_required ?? 2),
                'gpu_required' => $course->gpu_required ? 'yes' : 'no',
                'required_software' => $course->software_list ?? '',
                'difficulty_level' => $course->difficulty ?? 'medium',
            ],
            'ruangan' => $rooms->map(function ($room) {
                return [
                    'room_id' => (string) $room->id,
                    'room_name' => $room->name,
                    'capacity' => $room->computers->sum('computer_count'),
                    'ram_available_gb' => (float) ($room->ram_spec ?? 4),
                    'cpu_cores_available' => (int) ($room->cpu_spec ?? 2),
                    'gpu_available' => $room->has_gpu ? 'yes' : 'no',
                    'os_type' => $room->os_type ?? 'windows',
                ];
            })->toArray(),
        ];

        // Kirim API ke python
        $this->isLoadingAi = true;
        try {
            // ... kode request HTTP sebelumnya ...
            $response = Http::timeout(5)->post('http://127.0.0.1:8080/predict', $payload);

            if ($response->successful()) {
                $result = $response->json();

                // --- PERUBAHAN DISINI ---
                // Ambil semua hasil
                $allRecs = $result['recommendations'] ?? [];

                // Cukup ambil 3 urutan pertama (Top 3)
                // array_slice(array, offset, length)
                $this->recommendations = array_slice($allRecs, 0, 3);

                $this->dispatch('notify', ['type' => 'success', 'message' => 'AI berhasil merekomendasikan 3 ruangan terbaik.']);
            } else {
                $this->recommendations = [];
            }

        }catch (\Exception $e) {
            $this->recommendations = [];
            $this->showErrorAlert('Gagal mendapatkan rekomendasi dari AI.');
        }

        $this->isLoadingAi = false;
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

    public function selectRecommendedRoom($roomId)
    {
        // Reset array room_ids dan isi dengan yang dipilih
        $this->room_ids = [$roomId];

        // Trigger update occurrences agar jadwal di generate ulang
        $this->updatedRoomIds();

        $this->dispatch('notify', ['type' => 'info', 'message' => 'Ruangan diterapkan dari rekomendasi.']);
    }

    protected function rules(): array
    {
        $rule = [
            'room_ids' => ['required', 'array', 'min:1'],
            'room_ids.*' => ['required', 'exists:rooms,id'],
            'information' => ['nullable', 'string'],
            'repeat_count' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'time' => ['required', Rule::enum(TimeEnum::class)],
            'occurrences' => ['required', 'array', 'min:1'],
            'occurrences.*.start_date' => ['required', 'date'],
            'occurrences.*.time' => ['required', Rule::enum(TimeEnum::class)],
            'occurrences.*.room_ids' => ['required', 'array', 'min:1'],
        ];

        if ($this->activeTab === 'matakuliah') {
            $rule['lecturer_id'] = ['required', 'exists:lecturers,id'];
            $rule['course_id'] = ['required', 'exists:courses,id'];
        } else {
            $rule['lecturer_id'] = ['nullable', 'exists:lecturers,id'];
            $rule['category'] = ['required', Rule::enum(CategoryEnum::class)];
        }

        return $rule;
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

        $validator = Validator::make($this->all(), $this->rules());

        $validator->after(function ($validator) {
            foreach ($this->occurrences as $index => $occ) {

                $conflictExists = Schedule::where('start_date', $occ['start_date'])
                    ->where('time', $occ['time'])
                    ->whereNot('status', StatusEnum::REJECTED) // ✅ BENAR
                    ->whereHas('rooms', function ($q) use ($occ) {
                        $q->whereIn('rooms.id', $occ['room_ids']);
                    })
                    ->exists();

                if ($conflictExists) {
                    $validator->errors()->add(
                        "occurrences.$index.start_date",
                        'Sudah ada jadwal lain pada tanggal, waktu, dan ruangan yang sama.'
                    );
                }
            }
        });

        $validator->validate();

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
            $this->showErrorAlert('Terjadi kesalahan, silahkan coba lagi!');
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
