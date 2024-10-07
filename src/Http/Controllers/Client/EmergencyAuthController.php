<?php

namespace Nhd\Foundation\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Nahad\Auth\Events\DashboardLoginSuccess;
use Nahad\Auth\Http\Requests\Client\EmergencyLogin\LoginRequest;
use Nahad\Auth\Services\EmergencyLoginService;
use Nahad\Auth\Services\VerifyCodeService;

class EmergencyAuthController extends Controller {
    public function index() {
        return view('auth::client.emergency-login');
    }

    public function store(LoginRequest $request) {
        $data = $request->validated();

        if(EmergencyLoginService::useMaxAttempts($data['username'])) {
            return response('شما از حداکثر تعداد تلاش برای ورود اضطراری استفاده نموده اید.', 400);
        }

        $user = EmergencyLoginService::check($data['username'], $data['mobile']);

        if(!$user) {
            return response('کاربر مجاز نمی باشد', 400);
        }

        DashboardLoginSuccess::dispatch($user);

        $verifyCode = VerifyCodeService::send($user);

        return response(null)->cookie('2fa', encrypt($verifyCode->hash), config('auth.verify_code_session_expiration_time'));
    }
}