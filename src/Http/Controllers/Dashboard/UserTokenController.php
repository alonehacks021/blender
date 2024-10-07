<?php

namespace Nhd\Foundation\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Nhd\Foundation\Http\Requests\Dashboard\User\GenerateTokenRequest;
use Nhd\Foundation\Models\User;
use Nhd\Foundation\Support\Alert;

class UserTokenController extends Controller {

    public function tokens(User $user) {
        $this->authorize('tokens', $user);

        return view('auth::dashboard.user.tokens', [
            'user' => $user
        ]);
    }

    public function generate(GenerateTokenRequest $request, User $user) {
        $this->authorize('tokenCreate', $user);

        $token = $user->createToken($request->get('name'));

        return view('auth::dashboard.user.show-token', [
            'token' => $token
        ]);
    }

    public function destroy(User $user, $tokenId) {
        $this->authorize('tokenDelete', $user);

        $user->tokens()->where('id', $tokenId)->delete();

        Alert::add('توکن با موفقیت حذف گردید', Alert::SUCCESS);

        return redirect()->back();
    }
}