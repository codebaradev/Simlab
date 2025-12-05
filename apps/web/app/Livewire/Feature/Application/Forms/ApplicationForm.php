<?php

namespace App\Livewire\Feature\Application\Forms;

use App\Models\Room;
use App\Services\ApplicationService;
use App\Services\RoomService;
use Livewire\Component;

class ApplicationForm extends Component
{
    protected RoomService $rService;
    protected ApplicationService $appService;
    public Room $room;

    public $code;
    public $name;
    public $department_id;

    #[Reactive]
    public $editingId = null;

    protected $messages = [
        'code.required' => 'Kode program studi wajib diisi.',
        'code.max' => 'Kode program studi maksimal 10 karakter.',
        'name.required' => 'Nama program studi wajib diisi.',
        'name.max' => 'Nama program studi maksimal 100 karakter.',
        'department_id.required' => 'Jurusan wajib dipilih.',
        'department_id.exists' => 'Jurusan yang dipilih tidak valid.',
    ];

    public function boot(RoomService $rService, ApplicationService $appService)
    {
        $this->rService = $rService;
        $this->appService = $appService;
    }

    public function mount($room, $editingId = null, $formData = [])
    {
        $this->room = $room;
        $this->editingId = $editingId;

        if ($formData) {
            $this->name = $formData['name'] ?? '';
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
        ]);

        $validated['room_id'] = $this->room->id;

        try {
            if ($this->editingId) {
                $app = $this->appService->findById($this->editingId);
                $this->appService->update($app, $validated);
            } else {
                $this->appService->create($validated);
            }

            $this->dispatch('appSaved');
            $this->resetForm();

        } catch (\Exception $e) {
            $this->addError('code', $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->reset(['name']);
    }

    public function render()
    {
        return view('livewire.feature.application.forms.application-form');
    }
}
