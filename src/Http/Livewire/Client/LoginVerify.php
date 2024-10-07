<?php

namespace Nhd\Foundation\Http\Livewire\Client;

use Livewire\Component;
use Nhd\Foundation\Support\Alert;
use Nhd\Foundation\Services\VerifyCodeService;
use Nhd\Foundation\Foundation\Livewire\Events;

class LoginVerify extends Component
{
    use Events;

    public $code;

    public function render() {
        return view('auth::livewire.client.login-verify');
    }

    public function rules() {
        return [
            'code' => 'required|string|max:5'
        ];
    }

    public function check() {
        $this->validate();

        if(VerifyCodeService::useMaxAttemptChecks()) {
            Alert::add('شما از حداکثر تلاش خود برای صحت کد احراز هویت استفاده کردید. پس از منقضی شدن کد فعلی، کد جدید دریافت نمایید.', Alert::WARNING);

            return;
        }

        if(VerifyCodeService::checkCodeExpired()) {
            Alert::add('کد منقضی شده است', Alert::DANGER);

            return;
        }

        if(!VerifyCodeService::checkCodeValid($this->code)) {
            Alert::add('کد وارد شده نادرست می باشد', Alert::DANGER);

            return;
        }

        VerifyCodeService::codeVerified();

        return redirect()->to(config('auth.verify_code_redirect_url'));
    }

    public function resend() {
        if(!VerifyCodeService::isValid()) {
            return;
        }

        if(VerifyCodeService::useMaxSessionAttempts()) {
            Alert::add('شما از حداکثر تلاش های ارسال کد تایید استفاده کرده اید. با مدیر سامانه تماس بگیرید.', Alert::WARNING);

            return;
        }

        if(VerifyCodeService::hasActiveCode()) {
            Alert::add('کد ورود قبلی شما هنوز منقضی نشده است', Alert::INFO);

            return;
        }

        VerifyCodeService::resend();

        Alert::add('کد ورود جدید برای شما ارسال شد', Alert::SUCCESS);

        $this->dispatchBrowserEvent('resended', config('auth.verify_code_expiration_time'));
    }
}
