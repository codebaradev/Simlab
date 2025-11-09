<?php

namespace App\Livewire\Dashboard;

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
        if ($this->user->roles->contains('code', 'lbr')) {
            return view('livewire.dashboard.lbr-dashboard');
        }
        return view('livewire.dashboard.index');
    }
}
