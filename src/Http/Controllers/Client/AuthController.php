<?php

namespace Nhd\Foundation\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Nhd\Foundation\Models\User;
use Carbon\Carbon;
use Nhd\Foundation\Http\Requests\Client\User\AccountRequest;
use Nhd\Foundation\Models\UserChange;
use Nhd\Foundation\Services\AuthService;
use Nhd\Foundation\Support\Alert;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller {
    public function externalLogin(Request $request) {
        $authorized = $request->get('code');
        $csrfCode = $request->get('state');

        if($authorized && $csrfCode) {
            $user = AuthService::attempt($authorized, $csrfCode);

            if($user instanceof \Illuminate\Http\RedirectResponse) {
                return $user;
            }

            if($user) {
                return redirect('/panel');
            }
        }

        return AuthService::forward();
    }

    public function resetPassword(Request $request) {
        if($request->user()) {
            return redirect()->to('/');
        }

        return view('auth::client.reset-password');
    }

    public function verify(Request $request) {
        return view('auth::client.login-verify');
    }

    public function logout() {
        auth()->logout();

        return redirect()->to('/');
    }

    public function forward() {
        auth()->logout();
        
        return AuthService::forward();
    }

    public function accountGet(Request $request) {
        $this->authorize('account', User::class);

        return view('auth::client.account', [
            'user' => $request->user(),
        ]);    
    }

    public function accountPost(AccountRequest $request) {
        $this->authorize('account', User::class);
        
        $data = $request->validated();
        $user = $request->user();

        $user->update([
            'gender' => $data['gender']
        ]);

        UserChange::commit(UserChange::TYPE_CHANGE_GENDER);

        Alert::add('حساب کاربری با موفقیت بروزرسانی شد', Alert::SUCCESS);

        return redirect()->to('/');
    }

    public function userBanned() {
        return AuthService::forward();
    }

    public function application(Request $request) {
        $targetName = $request->get('target_name');

        if(!empty($targetName)) {
            if(auth()->check()) {
                return redirect()->route($targetName);
            }

            if($_COOKIE['sess_nahad_key'] ?? null) {
                $response = Http::withoutVerifying()
                    ->get(env('CENTRAL_AUTH_SERVICE_URL') . '/v2/Users/Profile/' . $_COOKIE['sess_nahad_key']);

                if($response->ok() && ($response->json('state') == 'ok')) {
                    $nationalCode = $response->json('user_notional_id');

                    $user = User::where('username', $nationalCode)
                        ->first();

                    if($user) {
                        auth()->login($user);
                        
                        return redirect()->route($targetName);
                    }
                }
            }
        }

        return abort(403);
    }
}