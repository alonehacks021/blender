<?php

namespace Nhd\Foundation\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Nhd\Foundation\Models\Session;

class SessionController extends Controller {

    public function index(Request $request) {
        $this->authorize('sessions', Session::class);

        $sessions = Session::has('user')
            ->with('user')
            ->filter()
            ->paginate(50);

        return view('auth::dashboard.session.index', [
            'sessions' => $sessions
        ]);
    }

    public function terminateAll() {
        $this->authorize('terminateAll', Session::class);

        Session::whereNotNull('id')->delete();

        return redirect()->to('/');
    }
}