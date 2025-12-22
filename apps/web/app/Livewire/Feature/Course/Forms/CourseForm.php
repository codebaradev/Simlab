<?php

namespace App\Livewire\Feature\Course\Forms;

use App\Enums\AcademicClass\SemesterEnum;
use App\Enums\UserRoleEnum;
use App\Models\Course;
use App\Models\User;
use App\Services\AcademicClassService;
use App\Services\CourseService;
use App\Traits\Livewire\WithAlertModal;
use Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CourseForm extends Component
{
    use WithAlertModal;

    protected AcademicClassService $acService;
    protected CourseService $cService;

    public ?Course $course;

    public User $user;

    public $name;
    public $sks;
    public $year;
    public $semester;
    public $class_id;
    public $lecturerIdf;

    public bool $isEditing;

    public bool $canEdit = true;

    public function boot(CourseService $cService, AcademicClassService $acService)
    {
        $this->cService = $cService;
        $this->acService = $acService;
    }

    public function mount($course = null)
    {
        $this->user = Auth::user();
        $this->course = $course;
        $this->isEditing = (bool) $this->course;
        $this->canEdit = !$this->user->roles->contains('code', UserRoleEnum::STUDENT->value);

        if ($this->isEditing) {
            $this->name = $this->course->name;
            $this->sks = $this->course->sks;
            $this->year = $this->course->year;
            $this->semester = $this->course->semester;
            $this->class_id = $this->course->academic_classes()->first()->id;
        }
    }

    public function save()
    {

        $validated = $this->validate([
            'class_id' => ['required', 'exists:academic_classes,id'],
            'name' => ['required', 'string', 'max:255'],
            'sks' => ['required', 'integer', 'min:1', 'lt:24'],
            'year' => ['required', 'max:9', 'regex:/^\d{4}\/\d{4}$/'],
            'semester' => ['required', Rule::enum(SemesterEnum::class)],
        ]);

        $validated['lecturer_id'] = $this->user->lecturer->id;

        try {
            if ($this->isEditing) {
                $this->cService->update($this->course, $validated);
            } else {
                $this->cService->create($validated);
                return $this->redirectRoute('course.index', navigate: true );
            }

            return $this->showSuccessAlert('Data Matakuliah Berhasil Diupdate');

        } catch (\Exception $e) {
            $this->showErrorAlert('Terjadi kesalahan, silahkan coba lagi!');
        }
    }

    public function getAcademicClassesProperty()
    {
        return $this->acService->getAll(
            [],
            isPaginated: false
        );
    }

    public function render()
    {
        $options = [
            'semester' => SemesterEnum::toArray()
        ];

        return view('livewire.feature.course.forms.course-form', [
            'options' => $options,
            'academicClasses' => $this->academicClasses
        ]);
    }
}
