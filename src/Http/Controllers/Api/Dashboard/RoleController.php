<?php

namespace Nhd\Foundation\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Nahad\Auth\Http\Resources\Dashboard\RoleSelect2;
use Nahad\Auth\Models\Role;

class RoleController extends Controller
{
    public function rolesSelect2(Request $request) {
        $this->authorize('select2List', Role::class);

        $roles = Role::query();

        $user = $request->user();
        if($user->cannot('select2ListAll', Role::class)) {
            $roles = $roles->where('owner_id', $user->id);
        }

        if($request->has('term')) {
            $term = str_replace(' ', '%', $request->get('term'));
            $roles = $roles->where('name', 'LIKE', '%'.$term.'%');
        }

        $roles = $roles->limit(20)->get();

        return [
            'results' => RoleSelect2::collection($roles)
        ];
    }
}
