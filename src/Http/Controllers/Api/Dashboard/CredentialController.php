<?php

namespace Nhd\Foundation\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Nhd\Foundation\Services\AuthService;

class CredentialController extends Controller
{
    public function check(Request $request) {
        return [
            'result' => AuthService::lastCredentialCheck()
        ];
    }
}
