<?php

namespace App\Livewire\Feature\StudyProgram\Forms;

use App\Models\StudyProgram;
use App\Services\StudyProgramService;
use App\Traits\Livewire\WithAlertModal;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class StudyProgramForm extends Component
{
    use WithAlertModal;

    protected StudyProgramService $spService;

    public ?StudyProgram $studyProgram;

    public $code;
    public $name;
    public $department_id;

    public bool $isEditing;

    protected $messages = [
        'code.required' => 'Kode program studi wajib diisi.',
        'code.max' => 'Kode program studi maksimal 10 karakter.',
        'name.required' => 'Nama program studi wajib diisi.',
        'name.max' => 'Nama program studi maksimal 100 karakter.',
        'department_id.required' => 'Jurusan wajib dipilih.',
        'department_id.exists' => 'Jurusan yang dipilih tidak valid.',
    ];

    public function boot(StudyProgramService $spService)
    {
        $this->spService = $spService;
    }

    public function mount($studyProgram = null)
    {
        $this->studyProgram = $studyProgram;
        $this->isEditing = (bool) $this->studyProgram;

        if ($this->isEditing) {
            $this->code = $this->studyProgram->code;
            $this->name = $this->studyProgram->name;
            $this->department_id = $this->studyProgram->department_id;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'code' => 'required|string|max:10|unique:study_programs,code'  . ($this->isEditing ? ',' . $this->studyProgram->id : '') ,
            'name' => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
        ]);

        try {
            if ($this->isEditing) {
                $this->spService->update($this->studyProgram, $validated, null, $this->department_id);
            } else {
                $this->spService->create($validated, null, $this->department_id);
                return $this->redirectRoute('study-program.index', navigate: true );
            }

            return $this->showSuccessAlert('Data Program Studi Berhasil Diupdate');

        } catch (\Exception $e) {
            $this->showErrorAlert('Terjadi kesalahan, silahkan coba lagi!');
        }
    }

    public function resetForm()
    {
        $this->reset(['code', 'name', 'department_id']);
        $this->resetErrorBag();
    }

    public function getDepartmentsProperty()
    {
        return \App\Models\Department::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.feature.study-program.forms.study-program-form', [
            'departments' => $this->departments,
        ]);
    }
}

