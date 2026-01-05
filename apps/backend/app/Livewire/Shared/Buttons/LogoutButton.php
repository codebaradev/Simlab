<?php

namespace App\Livewire\Shared\Buttons;

use App\Services\UserService;
use Livewire\Component;

class LogoutButton extends Component
{
    protected UserService $service;

    public function boot(UserService $service)
    {
        $this->service = $service;
    }

    public function logout()
    {
        $this->service->logout();
        $this->redirectRoute('login');
    }
    public function render()
    {
        return view('livewire.shared.buttons.logout-button');
    }
}
