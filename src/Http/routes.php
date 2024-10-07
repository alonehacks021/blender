<?php 
use Nhd\Foundation\Http\Middleware\EmergencyLoginHandle;

Route::namespace('Nhd\Foundation\Http\Controllers')
    ->middleware('web')
    ->group(function() {
        Route::get('application-login', 'Client\AuthController@application');

        Route::resource('emergency-login', 'Nhd\Foundation\Client\EmergencyAuthController')
            ->only('index', 'store')
            ->middleware(EmergencyLoginHandle::class);

        //Dashboard Routes
        Route::namespace('Dashboard')
            ->prefix('dashboard/auth')
            ->group(function() {
                Route::get('login', 'AuthController@loginGet')
                    ->name('login');
                Route::post('login', 'AuthController@loginPost');
                Route::get('logout', 'AuthController@logout');

                Route::get('verify/{hashCode}', 'AuthController@verifyGet');

                Route::middleware('auth')
                    ->group(function() {
                        Route::resource('users', 'UserController');
                        Route::get('users/destroy/{user}', 'UserController@destroy');
                        Route::get('users/roles/{user}', 'UserController@rolesGet');
                        Route::post('users/roles/{user}', 'UserController@rolesPost');
                        Route::get('users/tokens/{user}', 'UserTokenController@tokens');
                        Route::post('users/tokens/generate/{user}', 'UserTokenController@generate');
                        Route::get('users/tokens/destroy/{user}/{token_id}', 'UserTokenController@destroy');

                        Route::resource('users.user-2fa', 'User2FAController')
                            ->shallow();

                        Route::resource('users.sessions', 'UserSessionController')
                            ->only('index')
                            ->shallow();
                        Route::get('user-sessions/{session}', 'UserSessionController@destroy')->name('user-sessions.destroy');

                        Route::resource('sessions', 'SessionController')
                            ->only('index');
                        Route::get('sessions/terminate-all-sessions', 'SessionController@terminateAll')->name('terminate-all-sessions');

                        Route::resource('roles', 'RoleController');
                        Route::get('roles/permissions/{role}', 'RoleController@permissionsGet');
                        Route::post('roles/permissions/{role}', 'RoleController@permissionsPost');
                        Route::get('roles/destroy/{role}', 'RoleController@destroy');

                        Route::get('settings/defaults', 'SettingsController@defaultsGet');
                        Route::post('settings/defaults', 'SettingsController@defaultsPost');
                    });
            });

        Route::namespace('Api\Dashboard')
            ->prefix('/dashboard/ajax/auth')
            ->middleware('auth')
            ->group(function() {
                Route::get('users-select2', 'UserController@usersSelect2');
                Route::get('roles-select2', 'RoleController@rolesSelect2');
                Route::get('credential-check', 'CredentialController@check');
            });

        //Client Routes
        Route::prefix('auth')
            ->namespace('Client')
            ->group(function() {
                //Route::get('external-login', 'AuthController@externalLogin');

                Route::get('reset-password', 'AuthController@resetPassword');

                Route::get('verify', 'AuthController@verify')->name('verify-user');

                Route::get('logout', 'AuthController@logout');

                Route::middleware('auth')
                    ->group(function() {
                        Route::get('files/user-image/{user}', 'FileController@userImage');
                        
                        Route::get('account', 'AccountController@account')->name('auth-account');
                        // Route::post('account', 'AuthController@accountPost');
                    });
            });
    });

Route::get('forward-login', 'Nhd\Foundation\Http\Controllers\Client\AuthController@forward');

Route::get('user-banned', 'Nhd\Foundation\Http\Controllers\Client\AuthController@userBanned')->name('user-banned');