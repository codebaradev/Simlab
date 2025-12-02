<?php

namespace App\Livewire\Shared\Modals;

use Livewire\Component;

class AlertModal extends Component
{
    public $show = false;
    public $title = 'Informasi';
    public $message = '';
    public $type = 'info'; // success, error, warning, info
    public $actionText = 'Tutup';
    public $actionUrl = null;
    public $actionMethod = 'closeAlertModal';
    public $showCancelButton = false;
    public $cancelText = 'Batal';
    public $size = 'md'; // sm, md, lg, xl

    protected $listeners = [
        'showAlertModal' => 'showModal',
        'closeAlertModal' => 'closeModal'
    ];

    public function showModal($config = [])
    {
        $this->title = $config['title'] ?? 'Informasi';
        $this->message = $config['message'] ?? '';
        $this->type = $config['type'] ?? 'info';
        $this->actionText = $config['actionText'] ?? 'Tutup';
        $this->actionUrl = $config['actionUrl'] ?? null;
        $this->actionMethod = $config['actionMethod'] ?? 'closeAlertModal';
        $this->showCancelButton = $config['showCancelButton'] ?? false;
        $this->cancelText = $config['cancelText'] ?? 'Batal';
        $this->size = $config['size'] ?? 'md';

        $this->show = true;
    }

    public function closeModal()
    {
        $this->show = false;
        // Reset properties to defaults
        // $this->title = 'Informasi';
        // $this->message = '';
        // $this->type = 'info';
        // $this->actionText = 'Tutup';
        // $this->actionUrl = null;
        // $this->actionMethod = 'closeAlertModal';
        // $this->showCancelButton = false;
        // $this->cancelText = 'Batal';
        // $this->size = 'md';
    }

    public function performAction()
    {
        if ($this->actionUrl) {
            return redirect($this->actionUrl);
        }

        $this->dispatch($this->actionMethod);
        $this->closeModal();
    }

    public function cancel()
    {
        $this->closeModal();
        $this->dispatch('modalCancelled');
    }

    public function render()
    {
        return view('livewire.shared.modals.alert-modal');
    }
}
