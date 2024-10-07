<?php

namespace Nhd\Foundation\Services;

use GuzzleHttp\Client;
use Nhd\Foundation\Events\SSOUserLogged;
use Nhd\Foundation\Events\SSOUserLoging;
use Nhd\Foundation\Models\OrganizationalPost;
use Nhd\Foundation\Models\User;
use Nhd\Foundation\Models\UserRole;
use Nhd\Foundation\Models\VerifyCode;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Nhd\Foundation\Models\CredentialHistory;
use Nhd\Foundation\Models\UserChange;
use Nhd\Foundation\Services\LogService;
use Nahad\Notification\Services\SmsService;

class AuthService {
    private static $client = null;
    private static $token = null;
    private static $latestMessage = null;

    private static function client() {
        if(!self::$client) {
            self::$client = new Client([
                'base_uri' => config('auth.central_auth_service_url'),
                'timeout'  => config('auth.central_auth_service_timeout'),
                'http_errors' => false,
                'verify' => false,
            ]);
        }

        return self::$client;
    }

    public static function getToken() {
        return self::$token;
    }

    public static function setToken($token) {
        self::$token = $token;
    }

    public static function getLatestMessage() {
        return self::$latestMessage;
    }

    public static function passwordCredential($username, $password) {
        $response = self::client()->post('v1/Users/PasswordCredentials', [
            'form_params' => [
                'username' => $username,
                'password' => $password,
                'client_id' => config('auth.central_auth_service_client_id'),
                'client_secret' => config('auth.central_auth_service_client_secret'),
                'grant_type' => 'password',
                'scope' => 'login public',
            ]
        ]);

        if($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody()->getContents());

            if($result->state == 'ok') {
                self::$token = $result->data->access_token ?? null;

                return $result->data;
            }
        }

        return null;
    }
    
    public static function login($username, $password) {
        $response = self::client()->post('v1/Users/PasswordCredentials', [
            'form_params' => [
                'username' => $username,
                'password' => $password,
                'client_id' => config('auth.central_auth_service_client_id'),
                'client_secret' => config('auth.central_auth_service_client_secret'),
                'grant_type' => 'password',
                'scope' => 'login public',
            ]
        ]);

        if($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody()->getContents());

            if($result->state == 'ok') {
                self::$token = $result->data->access_token ?? null;

                return $result->data->username;
            }
        }

        return null;
    }

    public static function profile($mobile) {
        $response = self::client()->get('v1/Users/' . $mobile . '/Profile', [
            'headers' => [
                'Authorization' => self::$token,
            ]
        ]);

        if($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody()->getContents());

            if($result->state == 'ok') {
                $data = $result->data->records[0];

                return (object)[
                    'first_name' => $data->user_name,
                    'last_name' => $data->user_family,
                    'national_code' => $data->user_notional_id,
                    'mobile' => $data->user_mobile,
                    'user_network_code' => $data->user_network_code,
                    'sess_name' => $data->sess_name,
                    'sess_value' => $data->sess_value,
                ];
            }
        }

        return null;
    }

    public static function organizationPostCode($userNetworkCode) {
        $response = self::client()->get('v1/Users/' . $userNetworkCode . '/PersonnelInfo', [
            'headers' => [
                'Authorization' => self::$token,
            ]
        ]);

        if($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody()->getContents());

            if($result->state == 'ok') {
                $data = $result->data->records[0];

                return $data->person_post_code;
            }
        }

        return null;
    }

    public static function registerVerifyCode($mobile) {
        $response = self::client()->post('v1/Users/' . $mobile . '/RequestRegisterUser', [
            'form_params' => [
                'client_name' => config('auth.central_auth_service_client_name'),
                'client_version' => config('auth.central_auth_service_client_version')
            ],
        ]);

        $result = json_decode($response->getBody()->getContents());

        if($response->getStatusCode() == 200) {
            if($result->state == 'ok') {
                return $result->data->verify_id;
            }
        }
        else {
            self::$latestMessage = $result->description ?? 'Error';
        }

        return null;
    }

    public static function register($mobile, $verifyId, $verifyCode) {
        $response = self::client()->post('v1/Users/' . $mobile . '/RegisterUser', [
            'form_params' => [
                'user_mobile' => $mobile,
                'verify_id' => $verifyId,
                'sms_code' => $verifyCode,
                'client_id' => config('auth.central_auth_service_client_id'),
                'client_secret' => config('auth.central_auth_service_client_secret'),
                'grant_type' => 'password',
                'scope' => 'login public',
            ],
        ]);

        if($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody()->getContents());

            if($result->state == 'ok') {
                self::$token = $result->data->access_token ?? null;

                return $result->data->username;
            }
        }

        return null;
    }

    public static function updateProfile($mobile, $data) {
        $response = self::client()->post('v1/Users/' . $mobile . '/Profile', [
            'form_params' => [
                'user_mobile' => $mobile,
                'name' => $data['first_name'],
                'family' => $data['last_name'],
                'national_code' => $data['username'],
                'password' => $data['password'],
                'password_repeat' => $data['password']
            ],
            'headers' => [
                'Authorization' => self::$token,
            ],
        ]);

        $result = json_decode($response->getBody()->getContents());

        if($response->getStatusCode() == 200) {
            if($result->state == 'ok') {
                return true;
            }
        }
        else {
            self::$latestMessage = $result->description ?? 'Error';
        }

        return false;
    }

    public static function resetPasswordRequest($mobile) {
        $response = self::client()->post('v1/Users/' . $mobile . '/RequestResetPassword', [
            'form_params' => [
                'user_mobile' => $mobile,
                'client_name' => config('auth.central_auth_service_client_name'),
            ],
        ]);

        $result = json_decode($response->getBody()->getContents());

        if($response->getStatusCode() == 200) {
            if($result->state == 'ok') {
                return $result->data->verify_id;
            }
        }
        else {
            self::$latestMessage = $result->description ?? 'Error';
        }

        return false;
    }

    public static function resetPasswordConfirm($mobile, $verifyId, $code) {
        $response = self::client()->post('v1/Users/' . $mobile . '/ConfirmResetPassword', [
            'form_params' => [
                'user_mobile' => $mobile,
                'verify_id' => $verifyId,
                'sms_code' => $code,
            ],
        ]);

        $result = json_decode($response->getBody()->getContents());

        if($response->getStatusCode() == 200) {
            if($result->state == 'ok') {
                return $result->data->access_token;
            }
        }
        else {
            self::$latestMessage = $result->description ?? 'Error';
        }

        return false;
    }

    public static function resetPassword($mobile, $password) {
        $response = self::client()->post('v1/Users/' . $mobile . '/ResetPassword', [
            'form_params' => [
                'user_mobile' => $mobile,
                'password' => $password,
                'password_repeat' => $password,
            ],
            'headers' => [
                'Authorization' => self::$token
            ],
        ]);

        $result = json_decode($response->getBody()->getContents());

        if($response->getStatusCode() == 200) {
            if($result->state == 'ok') {
                return true;
            }
        }
        else {
            self::$latestMessage = $result->description ?? 'Error';
        }

        return false;
    }

    public static function authorizationCode($authorized) {
        $response = self::client()->post('v1/Systems/AuthorizationCode', [
            'form_params' => [
                'code' => $authorized,
                'grant_type' => 'authorization_code',
                'redirect_uri' => config('auth.central_auth_service_redirect_url'),
                'client_id' => config('auth.central_auth_service_client_id'),
                'client_secret' => config('auth.central_auth_service_client_secret'),
                'scope' => 'login public'
            ],
        ]);

        $result = json_decode($response->getBody()->getContents());

        if($response->getStatusCode() == 200) {
            if($result->state == 'ok') {
                self::$token = $result->data->access_token ?? null;
                
                return $result->data;
            }
        }
        
        self::$latestMessage = $result->description ?? 'Error';

        return null;
    }

    public static function attempt($authorized, $csrfCode) {
        try {
            if ($csrfCode != hash('md4', self::createState())) {
                self::$latestMessage = 'پارامتر state ارسال شده معتبر نمی باشد !';
            }
            else {
                $tokenInfo = self::authorizationCode($authorized);
                if($tokenInfo) {
                    $userInfo = AuthService::profile($tokenInfo->username);
                    if($userInfo) {
                        SSOUserLoging::dispatch([
                            'national_code' => $userInfo->national_code
                        ]);

                        $user = null;
                        $username = null;
                        $mobile = null;
                        if(config('auth.use_mobile_as_username')) {
                            $user = User::withTrashed()->where('mobile', $tokenInfo->username)->first();

                            $username = $tokenInfo->username;
                            $mobile = $tokenInfo->username;
                        }
                        else {
                            $user = User::withTrashed()->where('username', $userInfo->national_code)->first();

                            $username = $userInfo->national_code;
                            $mobile = $tokenInfo->username;
                        }

                        if($user) {
                            if($user->isBan()) {
                                \Redirect::route('user-banned', [
                                    'message_type' => 'error',
                                    'message_text' => trans('auth::auth-package-messages.user_banned_message'),
                                ])->send();
                    
                                exit;
                            }
                            
                            $user->update([
                                'username' => $username,
                                'mobile' => $mobile,
                                'first_name' => convert_characters($userInfo->first_name),
                                'last_name' => convert_characters($userInfo->last_name),
                                'ldap_login' => true,
                                'deleted_at' => null
                            ]);
                        } else {
                            $user = User::create([
                                'username' => $username,
                                'mobile' => $mobile,
                                'first_name' => convert_characters($userInfo->first_name),
                                'last_name' => convert_characters($userInfo->last_name),
                                'status' => User::STATUS_ACTIVE,
                                'type' => User::TYPE_USER,
                                'ldap_login' => true,
                                'password' => bcrypt(\Str::random(16)),
                            ]);
                        }
    
                        $organizationPostCode = self::organizationPostCode($userInfo->user_network_code);
    
                        $organizationalPost = OrganizationalPost::with('organizationPostRoles')
                            ->where('code', empty($organizationPostCode) ? null : $organizationPostCode) 
                            ->first();
    
                        if($organizationalPost && ($organizationalPost->organizationPostRoles->count() > 0)) {
                            UserRole::upsert($organizationalPost->organizationPostRoles->map(function($organizationalRole) use ($user) {
                                return [
                                    'user_id' => $user->id,
                                    'role_id' => $organizationalRole->role_id
                                ];
                            })->toArray(), [
                                'user_id', 'role_id'
                            ], [
                                'user_id', 'role_id'
                            ]);
                        }
            
                        SSOUserLogged::dispatch($user, [
                            'national_code' => $userInfo->national_code,
                        ], !empty($organizationPostCode));
    
                        if(get_option('two_step_login_client')) {
                            // VerifyCode::send($user);
    
                            // \Redirect::route('verify-user')->withCookie(cookie('2fa', encrypt([
                            //     'user_id' => $user->id,
                            //     'expired_at' => now()->addMinutes(20)->toDateTimeString(),
                            //     'hash' => \Hash::make(\Request::header('user-agent') . ':' . \Request::ip()),
                            // ]), 20))->send();
                            $verifyCode = VerifyCodeService::send($user);
    
                            return redirect()->route('verify-user')
                                ->withCookie(cookie('2fa', encrypt($verifyCode->hash), config('auth.verify_code_session_expiration_time')));
                        }
            
                        \Auth::login($user);

                        if(config('auth.check_gender')) {
                            $checkGenderDate = config('auth.check_gender_date');

                            if((!empty($checkGenderDate)) && $user->created_at->lessThanOrEqualTo(Carbon::parse($checkGenderDate . ' 23:59:59'))) {
                                $existsChange = $user->userChanges()->where('type', UserChange::TYPE_CHANGE_GENDER)->first();
                                
                                if(!$existsChange) {
                                    return redirect()->route('auth-account');
                                }
                            }
                        }

                        return $user;
                    }
                    else {
                        self::$latestMessage = AuthService::getLatestMessage();
                    }
                }
                else {
                    self::$latestMessage = AuthService::getLatestMessage();
                }
            }
        }
        catch(\Exception $error) {
            $code = \Str::random(32);
            Log::emergency('Auth Attempt Login: ' . $code);

            $jdate = jdate()->format('Y-m-d');

            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/auth-package-'.now()->toDateString().'.log'),
            ])->emergency($jdate . ': ' . $code . ': Login: ' . $error->getMessage());
            LogService::activity('exception-login')->withData(['date' => $jdate, 'code' => $code, 'message' => $error->getMessage()])->log();
            return false;
        }
        return false;
    }

    public static function forward() {
        if(config('auth.emergency_login') == 'YES') {
            return redirect()->route('emergency-login.index');
        }

        $data = str_replace([
            '%3A', '%2F', '%20'
        ], [
            ':', '/', ' '
        ], \Arr::query([
            'state' => hash('md4', self::createState()),
            'client_id' => config('auth.central_auth_service_client_id'),
            'redirect_uri' => config('auth.central_auth_service_redirect_url'),
            'scope' => 'login public',
            'response_type' => 'code',
            'message_type' => \Request::get('message_type', 'error'),
            'message_text' => \Request::get('message_text', get_option('login_page_message'))
        ]));

        $redirect_url = config('auth.central_auth_service_url') . '/v1/Systems/Login?' . $data;
        /*LogService::activity('forward-api')->setPath(('auth.central_auth_service_url') . '/v1/Systems/Login')
            ->withData([$data])->log();*/

        return redirect()->away($redirect_url);
    }

    public static function createState() {
        return 'A'.hash('crc32b', \Request::ip() . \Request::header('user-agent') . '-' . config('auth.central_auth_service_client_id') . '-' . config('auth.central_auth_service_client_secret'));
    }

    public static function QRCodeGenerate(): array|null {
        $response = self::client()->get('v1/Systems/QrCode', [
            'form_params' => [
                'client_name' => config('auth.central_auth_service_client_name'),
                'state' => self::createState(),
                'scope' => 'public'
            ],
        ]);

        $result = json_decode($response->getBody()->getContents());

        if($response->getStatusCode() == 200) {
            if($result->state == 'ok') {
                return [
                    'code' => $result->data->qr_code,
                    'image' => $result->data->qr_url,
                ];
            }
        }

        return null;
    }

    public static function QRCodeCheck($code, $nationalCode): bool {
        $response = self::client()->post('v1/Systems/QrCode', [
            'form_params' => [
                'qr_code' => $code,
                'client_name' => config('auth.central_auth_service_client_name'),
                'client_id' => config('auth.central_auth_service_client_id'),
                'state' => self::createState(),
                'scope' => 'public'
            ],
        ]);

        $result = json_decode($response->getBody()->getContents());

        if($response->getStatusCode() == 200) {
            if($result->state == 'ok') {
                return ($result->data->national_code ?? null) == $nationalCode;
            }
        }

        return false;
    }

    public static function lastCredentialCheck(): bool {
        $credentialHistory = CredentialHistory::where('user_id', auth()->id())
            ->latest('verified_at')
            ->first();

        return $credentialHistory && (
            now()->subMinutes(config('auth.credential_authenticator_trust_time'))->lessThan($credentialHistory->verified_at)
        );
    }
}