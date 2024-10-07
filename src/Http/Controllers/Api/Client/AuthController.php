<?php

namespace Nhd\Foundation\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Nhd\Foundation\Http\Requests\Api\Client\User\ExternalLoginRequest;
use Nhd\Foundation\Http\Resources\Api\Client\ExternalLogin;
use Nhd\Foundation\Models\User;
use Nhd\Foundation\Services\LDAPService;
use Nhd\Foundation\Services\UserService;

class AuthController extends Controller
{
    public function externalLogin(ExternalLoginRequest $request) {
        $this->authorize('externalLogin', User::class);

        $info = LDAPService::call('infoByNationalCode', [
            'national_code' => $request->get('national_code')
        ]);

        $username = LDAPService::call('getNetworkUsername', [
            'national_code' => $request->get('national_code')
        ]);

        if(!$info || !$username) {
            return response(null, 404);
        }
        
        $user = UserService::makeWithLdapData($username, \Str::random(16), $info);

        return new ExternalLogin($user);
    }
}
