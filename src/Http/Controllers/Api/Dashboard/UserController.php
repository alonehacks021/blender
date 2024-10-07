<?php

namespace Nhd\Foundation\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Nhd\Foundation\Http\Requests\Api\Client\User\NahadUsersRequest;
use Nhd\Foundation\Http\Resources\Dashboard\NahadUserSelect2;
use Nhd\Foundation\Models\User;
use Nhd\Foundation\Http\Resources\Dashboard\UserSelect2;
use Nahad\Special\Services\SpecialService;

class UserController extends Controller
{
    public function usersSelect2(Request $request) {
        $this->authorize('select2List', User::class);

        $users = User::query();

        $user = $request->user();
        if($user->cannot('select2ListAll', User::class)) {
            $users = $users->where('owner_id', $user->id);
        }

        if($request->has('term')) {
            $term = str_replace(' ', '%', $request->get('term'));
            $users = $users->where(function($query) use ($term) {
                $query->where('last_name', 'LIKE', '%'.$term.'%')
                    ->orWhere('first_name', 'LIKE', '%'.$term.'%')
                    ->orWhere('username', 'LIKE', $term.'%');
            });
        }

        $users = $users->limit(20)->get();

        return [
            'results' => UserSelect2::collection($users)
        ];
    }
}
