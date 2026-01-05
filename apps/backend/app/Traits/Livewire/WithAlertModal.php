<?php

namespace App\Traits\Livewire;

trait WithAlertModal
{
    /**
     * Show success alert modal
     *
     * @param string $message
     * @param string $title
     * @param string $actionText
     * @return void
     */
    public function showSuccessAlert(string $message, string $title = 'Berhasil!', string $actionText = 'Tutup')
    {
        $this->dispatch('showAlertModal', [
            'title' => $title,
            'message' => $message,
            'type' => 'success',
            'actionText' => $actionText,
        ]);
    }

    /**
     * Show error alert modal
     *
     * @param string $message
     * @param string $title
     * @param string $actionText
     * @return void
     */
    public function showErrorAlert(string $message, string $title = 'Error!', string $actionText = 'Tutup')
    {
        $this->dispatch('showAlertModal', [
            'title' => $title,
            'message' => $message,
            'type' => 'error',
            'actionText' => $actionText,
        ]);
    }

    /**
     * Show warning alert modal
     *
     * @param string $message
     * @param string $title
     * @param string $actionText
     * @return void
     */
    public function showWarningAlert(string $message, string $title = 'Peringatan!', string $actionText = 'Tutup')
    {
        $this->dispatch('showAlertModal', [
            'title' => $title,
            'message' => $message,
            'type' => 'warning',
            'actionText' => $actionText,
        ]);
    }

    /**
     * Show info alert modal
     *
     * @param string $message
     * @param string $title
     * @param string $actionText
     * @return void
     */
    public function showInfoAlert(string $message, string $title = 'Informasi', string $actionText = 'Tutup')
    {
        $this->dispatch('showAlertModal', [
            'title' => $title,
            'message' => $message,
            'type' => 'info',
            'actionText' => $actionText,
        ]);
    }

    /**
     * Show alert modal with custom configuration
     *
     * @param array $config
     * @return void
     */
    public function showAlert(array $config)
    {
        $this->dispatch('showAlertModal', $config);
    }

    /**
     * Show confirmation alert modal (with cancel button)
     *
     * @param string $message
     * @param string $title
     * @param string $actionText
     * @param string $cancelText
     * @param string|null $actionMethod
     * @return void
     */
    public function showConfirmAlert(
        string $message,
        string $title = 'Konfirmasi',
        string $actionText = 'Ya',
        string $cancelText = 'Batal',
        ?string $actionMethod = null
    ) {
        $this->dispatch('showAlertModal', [
            'title' => $title,
            'message' => $message,
            'type' => 'warning',
            'actionText' => $actionText,
            'cancelText' => $cancelText,
            'showCancelButton' => true,
            'actionMethod' => $actionMethod ?? 'closeAlertModal',
        ]);
    }

    /**
     * Show alert modal with redirect URL
     *
     * @param string $message
     * @param string $url
     * @param string $title
     * @param string $actionText
     * @param string $type
     * @return void
     */
    public function showAlertWithRedirect(
        string $message,
        string $url,
        string $title = 'Berhasil!',
        string $actionText = 'Lanjutkan',
        string $type = 'success'
    ) {
        $this->dispatch('showAlertModal', [
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'actionText' => $actionText,
            'actionUrl' => $url,
        ]);
    }
}


