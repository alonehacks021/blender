<?php

namespace Nhd\Foundation\Http\Livewire\Dashboard;

use Livewire\Component;
use Nhd\Foundation\Models\CredentialHistory;
use Nhd\Foundation\Services\AuthService;
use Nhd\Foundation\Foundation\Livewire\Events;

class SessionAuthenticator extends Component
{
    use Events;

    public string|null $password;
    public string $code;
    
    private string $image;

    public function mount(): void {
        $data = AuthService::QRCodeGenerate();

        $this->code = $data['code'];
        $this->image = $data['image'];
    }

    public function render()
    {
        return view('auth::livewire.dashboard.session-authenticator', [
            'code' => $this->code ?? null,
            'image' => $this->image ?? null,
        ]);
    }

    public function rules() {
        return [
            'password' => 'required|string|max:255',
        ];
    }

    public function hydrate() {
        if(AuthService::QRCodeCheck($this->code ?? null, auth()->user()->username)) {
            $this->makeCredentialHistory(CredentialHistory::TYPE_QR);
        }
    }

    public function check() {
        $this->validate();

        $user = auth()->user();

        $result = false;
        if($user->isAdmin()) {
            $result = \Hash::check($this->password, $user->password);
        }
        else {
            $result = AuthService::login(auth()->user()->username, $this->password);
        }
        
        if($result) {
            $this->makeCredentialHistory(CredentialHistory::TYPE_PASSWORD);
        }
        else {
            $this->addError('password', 'رمزعبور اشتباه می باشد');
        }

        $this->password = null;
    }

    private function makeCredentialHistory(int $type): void {
        CredentialHistory::create([
            'user_id' => auth()->id(),
            'type' => $type,
            'verified_at' => now()->toDateTimeString(),
        ]);

        $this->dispatchBrowserEvent('user-authenticated');
    }
}
