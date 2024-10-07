<?php

namespace Nhd\Foundation\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Nhd\Foundation\Models\User;

class FileController extends Controller {
    public function userImage(Request $request, User $user) {
        $file = null;

        if(auth()->user()->cannot('image', $user)) {
            if(request('type') == 'thumbnail') {
                $file = $user->getNoneThumbnailPath();
            }
            else {
                $file = $user->getNoneImagePath();
            }
        }
        else {
            $file = null;
            if(request('type') == 'thumbnail') {
                $file = $user->thumbnail_path;
            }
            else {
                $file = $user->image_path;
            }
        }

        return $file ? response()->download($file) : response()->download('vendor/auth/images/user.png');
    }
}