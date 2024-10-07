<?php

namespace Nhd\Foundation\Http\Livewire\Client;

use Livewire\Component;
use Illuminate\Validation\Rule;
use Nhd\Foundation\Models\User;
use Nhd\Foundation\Services\SpecialService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AccountUpdate extends Component
{
    use AuthorizesRequests;

    public $user;

    public function render()
    {
        $this->authorize('account', [User::class, $this->user]);

        return view('auth::livewire.client.account-update');
    }

    public function rules()
    {
        return [
            // 'user.first_name' => 'required|string|max:100',
            // 'user.last_name' => 'required|string|max:100',
            'user.birthday' => 'required|date_format:Y-m-d',
            // 'user.mobile' => ['required', 'starts_with:09', 'digits:11', 'unique:users,mobile,'.$this->user->id],
            'user.gender' => ['required', Rule::in(User::getGenders())],
        ];
    }

    public function save() {
        $this->authorize('account', [User::class, $this->user]);

        $this->validate();

        $this->user->update();

        // $this->dispatchBrowserEvent('saved');

        return redirect()->to('/');
    }
}
