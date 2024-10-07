<?php

namespace Nhd\Foundation\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Nhd\Foundation\Http\Requests\Dashboard\User\CreateRequest;
use Nhd\Foundation\Http\Requests\Dashboard\User\UpdateRequest;
use Nhd\Foundation\Models\User;
use Nhd\Foundation\oard\Support\Alert;
use Nhd\Foundation\Http\Request;
use Nhd\Foundation\Events\UserCreated;
use Nhd\Foundation\Http\Requests\Dashboard\User\RolesRequest;
use Nhd\Foundation\Models\Role;
use Nhd\Foundation\Services\SpecialService;
use Nhd\Foundation\Services\UserService;

class UserController extends Controller {

    public function index(Request $request) {
        $this->authorize('users', User::class);

        $users = User::latest();
        
        $user = $request->user();
        if($user->cannot('usersAll', User::class)) {
            $users = $users->where('owner_id', $user->id);
        }

        $users = $users->filter($request)->paginate(20)->appends($request->except('page'));

        return view('auth::dashboard.user.index', [
            'users' => $users
        ]);
    }

    public function create() {
        $this->authorize('create', User::class);

        return view('auth::dashboard.user.create');
    }

    public function store(CreateRequest $request) {
        $this->authorize('create', User::class);

        $data = $request->validated();
        $result = null;

        if($data['type'] == User::TYPE_USER) {
            $result = SpecialService::createUser(array_merge($data, [
                'national_code' => $data['username']
            ]));

            if(!$result) {
                Alert::add(SpecialService::getLatestMessage(), Alert::WARNING);
                return redirect()->back()->withInput();
            }
    
            if($result['exists']) {
                Alert::add('کاربر از قبل در سامانه کاربران وجود داشته است', Alert::WARNING);
                // return redirect()->back()->withInput();
            }
        }

        $owner = $request->user();
        $user = User::firstOrCreate([
            'username' => (($data['type'] == User::TYPE_USER) ? (config('auth.use_mobile_as_username') ? $data['mobile'] : $data['username']) : $data['username']),
        ], [
            'owner_id' => $owner->id ?? 1,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'mobile' => $data['mobile'],
            'gender' => $data['gender'],
            'status' => $data['status'],
            'type' => ($owner->can('createAdmin', User::class) ? ($data['type'] ?? User::TYPE_USER) : User::TYPE_USER),
            'email' => $data['email'],
            'password' => bcrypt($result['password'] ?? $data['password']),
        ]);

        UserService::saveImage($user, $request);

        UserCreated::dispatch($user, [
            'national_code' => $data['username'],
        ]);

        if($user->type == User::TYPE_ADMIN) {
            Alert::add('کاربر با موفقیت ایجاد شد', Alert::SUCCESS);
        }
        else if($user->type == User::TYPE_USER) {
            $password = $result['password'] ?? null;

            if($password) {
                Alert::add("کاربر با موفقیت افزوده شد. رمز عبور کاربر {} می باشد. این رمز غیر قابل بازیابی می باشد.", Alert::SUCCESS);
            }
            else {
                Alert::add("اعطلاعات کاربر بروزرسانی گردید", Alert::SUCCESS);
            }
        }

        return redirect("/dashboard/auth/users/$user->id/edit");
    }

    public function show(User $user) {
        $this->authorize('show', $user);

        return view('auth::dashboard.user.show', [
            'user' => $user
        ]);
    }

    public function edit(User $user) {
        $this->authorize('update', $user);

        return view('auth::dashboard.user.update', [
            'user' => $user
        ]);
    }

    public function update(UpdateRequest $request, User $user) {
        $this->authorize('update', $user);

        $data = $request->validated();

        $currentUser = $request->user();
        if($user->type == User::TYPE_ADMIN) {
            $user->update([
                'username' => $data['username'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'mobile' => $data['mobile'],
                'gender' => $data['gender'],
                'status' => ($currentUser->can('changeStatus', $user) ? $data['status'] : $user->status),
                'email' => $data['email'],
                'password' => isset($data['password']) ? bcrypt($data['password']) : $user->password,
            ]);

            UserService::saveImage($user, $request);
        }
        else {
            $user->update([
                'gender' => $data['gender'],
                'status' => ($currentUser->can('changeStatus', $user) ? $data['status'] : $user->status),
            ]);

            $result = SpecialService::updateUser($user->mobile, [
                'mobile' => $user->mobile,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'national_code' => $user->meta?->national_code ?? $user->username,
            ]);

            if($result) {
                $user->update([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                ]);
            }
            else {
                Alert::add(SpecialService::getLatestMessage(), Alert::WARNING);
            }
        }

        Alert::add('اطلاعات قابل ویرایش کاربر با موفقیت بروزرسانی شد', Alert::SUCCESS);

        return redirect()->back();
    } 

    public function destroy(User $user) {
        $this->authorize('delete', $user);

        if(($user->username == 'admin') || ($user->id == 1)) {
            Alert::add('شما کاربر اصلی را نمیتوانید حذف کنید', Alert::DANGER);
        }
        else {
            $user->delete();
            Alert::add('کاربر با موفقیت حذف شد', Alert::SUCCESS);
        }

        return redirect()->back();
    }

    public function rolesGet($userId) {
        $user = User::findOrFail($userId);

        $this->authorize('updateRoles', $user);

        $roles = Role::orderBy('name')
            ->get();
        $userRoles = $user->userRoles->pluck('role_id')->toArray();

        return view('auth::dashboard.user.roles', [
            'user' => $user,
            'roles' => $roles,
            'user_roles' => $userRoles
        ]);
    }

    public function rolesPost(RolesRequest $request, $userId) {
        $user = User::findOrFail($userId);

        $this->authorize('updateRoles', $user);

        $user->userRoles()->delete();

        $newRoles = array_map(function($roleId) use ($user) {
            return [
                'user_id' => $user->id,
                'role_id' => $roleId
            ];
        }, $request->get('roles', []));

        $user->userRoles()->insert($newRoles);

        Alert::add('نقش های کاربر با موفقیت بروزرسانی شد', Alert::SUCCESS);

        return redirect()->back();
    }
}