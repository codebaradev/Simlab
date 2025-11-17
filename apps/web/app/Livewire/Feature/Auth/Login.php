<?php

namespace App\Livewire\Feature\Auth;

use App\Models\User;
use App\Services\UserService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    private UserService $userService;

    public ?User $user;

    public $username;
    public $password;

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function mount()
    {

    }

    public function login()
    {
        $validated = $this->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string',  'max:255'],
        ]);

        try {
            $this->user = $this->userService->login($validated);
        } catch (\Throwable $e) {
            $this->addError('general', $e->getMessage());
            return;
        }

        return $this->redirect('/dashboard');
    }

    public function render()
    {
        return view('livewire.feature.auth.login');
    }
}
