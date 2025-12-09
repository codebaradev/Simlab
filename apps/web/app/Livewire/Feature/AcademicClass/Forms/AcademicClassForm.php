<?php

namespace App\Livewire\Feature\AcademicClass\Forms;

use App\Enums\AcademicClass\SemesterEnum;
use App\Enums\AcademicClass\TypeEnum;
use App\Models\AcademicClass;
use App\Models\StudyProgram;
use App\Services\AcademicClassService;
use App\Services\StudyProgramService;
use App\Traits\Livewire\WithAlertModal;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AcademicClassForm extends Component
{
    use WithAlertModal;

    protected StudyProgramService $spService;
    protected AcademicClassService $acService;

    public StudyProgram $studyProgram;
    public ?AcademicClass $academicClass;

    public $generation;
    public $type;
    public $name;
    public $code;
    public $year;
    public $semester;

    public bool $isEditing;

    public function boot(StudyProgramService $spService, AcademicClassService $acService)
    {
        $this->spService = $spService;
        $this->acService = $acService;
    }

    public function mount($studyProgram, $academicClass = null)
    {
        $this->studyProgram = $studyProgram;
        $this->academicClass = $academicClass;
        $this->isEditing = (bool) $this->academicClass;

        if ($this->isEditing) {
            $this->generation = $this->academicClass->generation;
            $this->type = $this->academicClass->type;
            $this->name = $this->academicClass->name;
            $this->code = $this->academicClass->code;
            $this->year = $this->academicClass->year;
            $this->semester = $this->academicClass->semester;
        }
    }

    public function save()
    {
        $uniqueCodeRule = $this->isEditing
            ? Rule::unique('academic_classes', 'code')->ignore($this->academicClass->id)
            : Rule::unique('academic_classes', 'code');

        $validated = $this->validate([
            'generation' => ['required', 'integer', 'digits:4', 'between:1900,2100'],
            'type' => ['required', Rule::enum(TypeEnum::class)],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', $uniqueCodeRule],
            'year' => ['required', 'integer', 'between:1900,2100'],
            'semester' => ['required', Rule::enum(SemesterEnum::class)],
        ]);

        try {
            if ($this->isEditing) {
                $this->acService->update($this->academicClass, $validated, null);
            } else {
                $this->acService->create($validated, null);
                return $this->redirectRoute('study-program.index', navigate: true );
            }

            return $this->showSuccessAlert('Data Program Studi Berhasil Diupdate');

        } catch (\Exception $e) {
            $this->showErrorAlert('Terjadi kesalahan, silahkan coba lagi!');
        }
    }

    public function render()
    {
        $options = [
            'type' => TypeEnum::toArray(),
            'semester' => SemesterEnum::toArray()
        ];

        return view('livewire.feature.academic-class.forms.academic-class-form', [
            'options' => $options
        ]);
    }
}
