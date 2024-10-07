<?php

return [
    // Auth package settings
    'auth' => [
        'emergency_login' => env('EMERGENCY_LOGIN'),
        'emergency_login_max_attempts_per_day' => env('EMERGENCY_LOGIN_MAX_ATTEMPTS_PER_DAY', 1),

        'verify_code_expiration_time' => env('VERIFY_CODE_EXPIRATION_TIME', 3), // minutes
        'verify_code_redirect_url' => env('VERIFY_CODE_REDIRECT_URL', '/'),
        'verify_code_session_expiration_time' => env('VERIFY_CODE_SESSION_EXPIRATION_TIME', 20), // minutes
        'verify_code_max_attempts' => env('VERIFY_CODE_MAX_ATTEMPTS', 3),
        'verify_code_max_checks' => env('VERIFY_CODE_MAX_CHECKS', 3),

        'credential_authenticator_trust_time' => env('CREDENTIAL_AUTHENTICATOR_TRUST_TIME', 60),

        'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', 3),

        'ldap_service_url' => env('LDAP_SERVICE_URL'),
        'ldap_service_username' => env('LDAP_SERVICE_USERNAME'),
        'ldap_service_password' => env('LDAP_SERVICE_PASSWORD'),
        'ldap_service_timeout' => env('LDAP_SERVICE_TIMEOUT', 20),

        'central_auth_service_url' => env('CENTRAL_AUTH_SERVICE_URL'),
        'central_auth_service_client_id' => env('CENTRAL_AUTH_SERVICE_CLIENT_ID'),
        'central_auth_service_client_secret' => env('CENTRAL_AUTH_SERVICE_CLIENT_SECRET'),
        'central_auth_service_client_name' => env('CENTRAL_AUTH_SERVICE_CLIENT_NAME'),
        'central_auth_service_client_version' => env('CENTRAL_AUTH_SERVICE_CLIENT_VERSION', '1.0'),
        'central_auth_service_timeout' => env('CENTRAL_AUTH_SERVICE_TIMEOUT', 20),
        'central_auth_service_redirect_url' => env('CENTRAL_AUTH_SERVICE_REDIRECT_URL'),

        'auth_api_special_url' => env('AUTH_API_SPECIAL_URL'),
        'auth_api_special_client_id' => env('AUTH_API_SPECIAL_CLIENT_ID'),
        'auth_api_special_client_secret' => env('AUTH_API_SPECIAL_CLIENT_SECRET'),
        'auth_api_special_username' => env('AUTH_API_SPECIAL_USERNAME'),
        'auth_api_special_password' => env('AUTH_API_SPECIAL_PASSWORD'),
        'auth_api_special_client_name' => env('AUTH_API_SPECIAL_CLIENT_NAME'),
        'auth_api_special_client_version' => env('AUTH_API_SPECIAL_CLIENT_VERSION', '1.0'),
        'auth_api_special_timeout' => env('AUTH_API_SPECIAL_TIMEOUT', 20),
        'auth_api_special_redirect_url' => env('AUTH_API_SPECIAL_REDIRECT_URL'),

        'guest_id_expiration' => 15, // minutes

        'logout_redirect' => env('LOGOUT_REDIRECT', env('APP_URL')),

        'ec_service_url' => env('EC_SERVICE_URL'),
        'ec_service_timeout' => env('EC_SERVICE_TIMEOUT', 20),
        'ec_service_username' => env('EC_SERVICE_USERNAME'),
        'ec_service_password' => env('EC_SERVICE_PASSWORD'),

        'use_mobile_as_username' => false,
        'check_gender' => false,
        'check_gender_date' => '2022-10-10', // user created_at before this date

        'check_complete_profile' => false,
        'complete_profile_layout' => 'auth::client.simple-layout',

        'send_mobile_notification' => true,
        'enabled_2fa' => true,
    ],

    // Dashboard package settings
    'dashboard' => [
        'layout' => 'dashboard::adminlte-layout',
        'login_layout' => 'dashboard::login.adminlte',

        'adminlte' => [
            'main_header' => 'navbar-indigo',
            'sidebar_header' => 'navbar-default',
            'sidebar_header_text' => '',
        ],
    ],
];