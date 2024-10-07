<?php

namespace Nhd\Foundation\Http\Livewire\Dashboard;

use Livewire\Component;
use Nhd\Foundation\Models\CredentialHistory;
use Nhd\Foundation\Services\AuthService;
use Nhd\Foundation\Foundation\Livewire\Events;

class CredentailCheck extends Component
{
    use Events;

    public string|null $title;
    public string|null $btn;
    public string|null $class;
    public string|null $icon;

    public function render()
    {
        return view('auth::livewire.dashboard.credential-check');
    }

    public function check() {
        if(AuthService::lastCredentialCheck()) {
            $this->dispatchBrowserEvent('trusted-credential');
        }
        else {
            $this->dispatchBrowserEvent('untrusted-credential');
        }
    }
}
