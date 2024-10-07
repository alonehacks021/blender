<?php

namespace Nhd\Foundation\Http\Livewire\Client;

use CreateVerifyCodesTable;
use Livewire\Component;
use Hashids\Hashids;
use Nhd\Foundation\Models\ResetPassword as ResetPasswordModel;
use Nhd\Foundation\Models\User;
use Nhd\Foundation\Models\VerifyCode;
use Nhd\Foundation\Rules\PasswordRule;
use Nhd\Foundation\Services\AuthService;
use Nahad\Log\Services\LogService;
use Nhd\Foundation\Support\Alert;

class ResetPassword extends Component
{
    public $mobile;
    public $captcha;
    public $code;
    public $password;
    public $password_confirmation;
    public $level;

    public function mount() {
        $this->level = 1;
    }

    public function render()
    {
        return view('auth::livewire.client.reset-password');
    }

    public function checkMobile() {
        $this->validate([
            'mobile' => 'required|numeric|digits:11|starts_with:09',
            'captcha' => 'required|captcha'
        ]);

        $verifyId = AuthService::resetPasswordRequest($this->mobile);

        if($verifyId) {
            ResetPasswordModel::where('mobile', $this->mobile)
                ->delete();

            ResetPasswordModel::create([
                'mobile' => $this->mobile,
                'verify_id' => $verifyId,
                'expired_at' => now()->timestamp + 118
            ]);

            $this->level++;

            Alert::add('کد اعتبارسنجی به تلفن همراه شما ارسال شد', Alert::SUCCESS);
        }
        else {
            Alert::add(AuthService::getLatestMessage(), Alert::DANGER);
        }
    }

    public function checkCode() {
        $this->validate([
            'code' => 'required|numeric|digits:5',
        ]);

        $resetPassword = ResetPasswordModel::where('mobile', $this->mobile)
            ->first();

        if($resetPassword) {
            $accessToken = AuthService::resetPasswordConfirm($resetPassword->mobile, $resetPassword->verify_id, $this->code);

            if($accessToken) {
                ResetPasswordModel::where('mobile', $resetPassword->mobile)
                    ->update([
                        'access_token' => $accessToken,
                    ]);

                Alert::add('شما 2 دقیقه وقت دارید رمزعبور جدید را وارد نمایید، در غیر اینصورت میبایست مراحل بازنشانی رمزعبور را مجدد بگذرانید.', Alert::INFO);

                $this->level++;
            }
            else {
                Alert::add(AuthService::getLatestMessage(), Alert::DANGER);
            }
        }
        else {
            Alert::add('خطایی رخ داده است', Alert::DANGER);
        }
    }

    public function setPassword() {
        $this->validate([
            'password' => ['required', 'confirmed', 'min:10', new PasswordRule()]
        ]);

        $resetPassword = ResetPasswordModel::where('mobile', $this->mobile)
            ->first();

        if($resetPassword) {
            AuthService::setToken($resetPassword->access_token);
            $result = AuthService::resetPassword($resetPassword->mobile, $this->password);

            if($result) {
                $this->level++;

                ResetPasswordModel::where('mobile', $this->mobile)
                    ->delete();

                Alert::add('رمزعبور شما با موفقیت بازنشانی شد', Alert::SUCCESS);

                $this->dispatchBrowserEvent('password-reseted');
            }
            else {
                Alert::add(AuthService::getLatestMessage(), Alert::DANGER);
            }
        }
        else {
            Alert::add('خطایی رخ داده است', Alert::DANGER);
        }
    }

    public function resendCode() {
        $resetPassword = ResetPasswordModel::where('mobile', $this->mobile)
            ->first();

        if($resetPassword && ($resetPassword->expired_at <= now()->timestamp)) {
            $verifyId = AuthService::resetPasswordRequest($resetPassword->mobile);

            if($verifyId) {
                ResetPasswordModel::where('mobile', $resetPassword->mobile)
                    ->update([
                        'verify_id' => $verifyId,
                        'expired_at' => now()->timestamp + 118
                    ]);

                Alert::add('کد اعتبار سنجی جدید مجدد ارسال گردید', Alert::PRIMARY);
            }
        }
        else {
            $remain = $resetPassword->expired_at - now()->timestamp;

            Alert::add($remain . ' ثانیه دیگر امتحان نمایید', Alert::INFO);
        }
    }
}
