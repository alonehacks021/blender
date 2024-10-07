<?php
namespace Nhd\Foundation\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Nhd\Foundation\Http\Requests\Dashboard\Role\CreateRequest;
use Nhd\Foundation\Http\Requests\Dashboard\Role\UpdateRequest;
use Nhd\Foundation\Models\Role;

use Illuminate\Http\Request;
use Nhd\Foundation\Http\Requests\Dashboard\Role\PermissionsRequest;
use Nhd\Foundation\Models\Permission;
use Nhd\Foundation\Support\Alert;

class RoleController extends Controller {
    public function index(Request $request) {
        $this->authorize('roles', Role::class);

        $roles = Role::latest();

        $user = $request->user();
        if($user->cannot('rolesAll', Role::class)) {
            $roles = $roles->where('owner_id', $user->id);
        }

        $roles = $roles->filter($request)->paginate(20);

        return view('auth::dashboard.role.index', [
            'roles' => $roles
        ]);
    }

    public function create() {
        $this->authorize('create', Role::class);

        return view('auth::dashboard.role.create');
    }

    public function store(CreateRequest $request) {
        $this->authorize('create', Role::class);

        $data = $request->validated();

        $owner = $request->user();
        $role = Role::create([
            'owner_id' => $owner->id ?? 1,
            'name' => $data['name']
        ]);

        Alert::add('افزودن نقش با موفقیت انجام شد', Alert::SUCCESS);

        return redirect("dashboard/auth/roles/permissions/$role->id");
    }

    public function edit(Role $role) {
        $this->authorize('update', $role);

        return view('auth::dashboard.role.update', [
            'role' => $role
        ]);
    }

    public function update(UpdateRequest $request, Role $role) {
        $this->authorize('update', $role);

        $data = $request->validated();

        $role->update([
            'name' => $data['name']
        ]);

        Alert::add('بروزرسانی نقش با موفقیت انجام شد', Alert::SUCCESS);

        return redirect()->back();
    }

    public function destroy(Role $role) {
        $this->authorize('delete', $role);

        $role->delete();

        Alert::add('حذف نقش با موفقیت انجام شد', Alert::SUCCESS);

        return redirect()->back();
    }

    public function permissionsGet(Role $role) {
        $this->authorize('updatePermissions', $role);

        $rolePermissions = $role->rolePermissions->pluck('permission_id')->toArray();
        $namespaces = Permission::orderBy('namespace', 'ASC')
            ->orderBy('order', 'ASC')
            ->get()
            ->groupBy(['namespace']);
        
        $sortedNamespaces = $namespaces->sortByDesc(function ($item) {
            return count($item);
        });

        return view('auth::dashboard.role.permissions', [
            'role' => $role,
            'role_permissions' => $rolePermissions,
            'namespaces' => $sortedNamespaces->all()
        ]);
    }

    public function permissionsPost(PermissionsRequest $request, Role $role) {
        $this->authorize('updatePermissions', $role);

        $role->rolePermissions()->delete();
        
        $newRoles = array_map(function($permissionId) use ($role) {
            return [
                'role_id' => $role->id,
                'permission_id' => $permissionId
            ];
        }, $request->get('permissions', []));

        $role->rolePermissions()->insert($newRoles);

        Alert::add('تغییرات اعمال گردید', Alert::SUCCESS);

        return redirect()->back();
    }
}