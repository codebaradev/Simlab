<?php

namespace App\Livewire\Feature\Dashboard;

use App\Enums\UserRoleEnum;
use App\Traits\WithUser;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use WithUser;
    public function mount()
    {
        $this->user = Auth::user();
    }

    public function render()
    {
        return view('livewire.feature.dashboard.index', [
            'role' => $this->user->roles->first()->code
        ]);
    }
}
