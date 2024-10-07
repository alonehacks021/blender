<?php

use Nahad\Auth\Services\AuthService;

if(!function_exists('last_credential_check')) {
    function last_credential_check(): bool {
        return AuthService::lastCredentialCheck();
    }
}