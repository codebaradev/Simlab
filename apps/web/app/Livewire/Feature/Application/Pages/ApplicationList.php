<?php

namespace App\Livewire\Feature\Application\Pages;

use App\Models\Room;
use App\Services\ApplicationService;
use App\Services\RoomService;
use App\Traits\Livewire\WithAlertModal;
use Livewire\Component;

class ApplicationList extends Component
{
    use WithAlertModal;

    protected RoomService $rService;
    protected ApplicationService $appService;

    public Room $room;
    public $showFormModal = false;
    public $editingAppId = null;
    public $formData = [];

    protected $listeners = [
        'appSaved' => 'handleAppSaved',
        'appDeleted' => 'handleAppDeleted',
        'showEditForm' => 'showEditForm',
        'showCreateForm' => 'showCreateForm',
        'closeFormModal' => 'closeFormModal'
    ];

    public function boot(RoomService $rService,  ApplicationService $appService)
    {
        $this->rService = $rService;
        $this->appService = $appService;
    }

    public function mount($roomId)
    {
        $this->room = $this->rService->findById($roomId);
        $this->formData = $this->emptyForm();
    }

    public function showCreateForm()
    {
        $this->editingDepartment = null;
        $this->formData = $this->emptyForm();
        $this->showFormModal = true;
    }

    public function showEditForm($appId)
    {
        $this->editingAppId = $appId;
        $app = $this->appService->findById($appId);

        $this->formData = [
            'name' => $app->name,
        ];
        $this->showFormModal = true;
    }

    public function closeFormModal()
    {
        $this->showFormModal = false;
        $this->editingStudyProgram = null;
        $this->formData = $this->emptyForm();
    }

    private function emptyForm()
    {
        return [
            'code' => '',
            'name' => '',
            'department_id' => null,
        ];
    }

    public function handleAppSaved()
    {
        $this->closeFormModal();
        $this->dispatch('refresh-table');
        $this->showSuccessAlert('Data aplikasi berhasil disimpan.');
    }

    public function handleAppDeleted()
    {
        $this->dispatch('refresh-table');
        $this->showSuccessAlert('Data aplikasi berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.feature.application.pages.application-list');
    }
}
