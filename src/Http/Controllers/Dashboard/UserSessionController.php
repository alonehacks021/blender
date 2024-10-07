<?php

namespace Nhd\Foundation\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Nhd\Foundation\Models\User;
use Illuminate\Http\Request;
use Nhd\Foundation\Models\Session;
use Nahad\Dashboard\Support\Alert;

class UserSessionController extends Controller {

    public function index(Request $request, User $user) {
        $this->authorize('sessions', $user);

        return view('auth::dashboard.user.session.index', [
            'user' => $user
        ]);
    }

    public function destroy(Session $session) {
        $this->authorize('sessionsDelete', $session->user);

        $session->delete();

        Alert::add('نشست کاربر با موفقیت حذف گردید', Alert::SUCCESS);

        return back();
    }
}