<?php

namespace App\Livewire\Feature\StudyProgram\Forms;

use App\Services\StudyProgramService;
use Livewire\Component;

class StudyProgramForm extends Component
{
    protected StudyProgramService $spService;

    public $code;
    public $name;
    public $department_id;
    public $editingId = null;

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

    public function mount($editingId = null, $formData = [])
    {
        $this->editingId = $editingId;

        if ($formData) {
            $this->code = $formData['code'] ?? '';
            $this->name = $formData['name'] ?? '';
            $this->department_id = $formData['department_id'] ?? null;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'code' => 'required|string|max:10|unique:study_programs,code'  . ($this->editingId ? ',' . $this->editingId : '') ,
            'name' => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
        ]);

        try {
            if ($this->editingId) {
                $studyProgram = $this->spService->findById($this->editingId);
                $this->spService->update($studyProgram, $validated, null, $this->department_id);
            } else {
                $this->spService->create($validated, null, $this->department_id);
            }

            $this->dispatch('studyProgramSaved');
            $this->resetForm();

        } catch (\Exception $e) {
            $this->addError('code', $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->reset(['code', 'name', 'department_id', 'editingId']);
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

