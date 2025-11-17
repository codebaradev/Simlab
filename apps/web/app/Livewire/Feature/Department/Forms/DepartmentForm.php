<?php

namespace App\Livewire\Feature\Department\Forms;

use App\Services\DepartmentService;
use Livewire\Component;

class DepartmentForm extends Component
{
    public $code;
    public $name;
    public $editingId = null;

    protected $rules = [
        'code' => 'required|string|max:10',
        'name' => 'required|string|max:100',
    ];

    protected $messages = [
        'code.required' => 'Kode jurusan wajib diisi.',
        'code.max' => 'Kode jurusan maksimal 10 karakter.',
        'name.required' => 'Nama jurusan wajib diisi.',
        'name.max' => 'Nama jurusan maksimal 100 karakter.',
    ];

    public function mount($editingId = null, $formData = [])
    {
        $this->editingId = $editingId;

        if ($formData) {
            $this->code = $formData['code'] ?? '';
            $this->name = $formData['name'] ?? '';
        }
    }

    public function save()
    {
        $this->validate();

        $departmentService = app(DepartmentService::class);

        try {
            $data = [
                'code' => $this->code,
                'name' => $this->name,
            ];

            if ($this->editingId) {
                $department = \App\Models\Department::find($this->editingId);
                $departmentService->update($department, $data);
            } else {
                $departmentService->create($data);
            }

            $this->dispatch('departmentSaved');
            $this->resetForm();

        } catch (\Exception $e) {
            $this->addError('code', $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->reset(['code', 'name', 'editingId']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.feature.department.forms.department-form');
    }
}
