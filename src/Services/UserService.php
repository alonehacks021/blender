<?php

namespace Nhd\Foundation\Services;

use Intervention\Image\ImageManagerStatic as Image;
use Nhd\Foundation\Events\UserCreated;
use Nhd\Foundation\Models\OrganizationalPost;
use Nhd\Foundation\Models\OrganizationalPostRole;
use Nhd\Foundation\Models\User;
use Nhd\Foundation\Models\UserRole;
use Nhd\Foundation\Models\Dashboard\Option;
use Nhd\Foundation\Support\Alert;

class UserService {
    private static $cache = [];
    private static $guestId = null;

    public static function saveImage($user, $request) {
        if($request->hasFile('image')) {
            $storage = \Storage::disk('users-images');

            if($user->image) {
                $storage->delete($user->image);
                $storage->delete(str_replace('.', '-thumb.', $user->image));
            }

            $file = $request->file('image');
            $name = \Str::random(29);
            $extension = $file->getClientOriginalExtension();
            $path = date('Y/m/d');

            $image = $storage->putFileAs($path, $file, "$name.$extension");

            try {
                $img = Image::make($storage->path($image));
                $img->resize(300, 400);
                $img->save();

                $thumbnail = $storage->putFileAs($path, $file, "$name-thumb.$extension");

                $img = Image::make($storage->path($thumbnail));
                $img->resize(100, 100);
                $img->save();
            }
            catch(\Exception $e) {
                Alert::add('تصویر دارای نقص میباشد.', Alert::WARNING);
            }

            $user->update([
                'image' => $image
            ]);
        }
    }

    public static function saveImageFromUrl($user, $url) {
        $oldImageName = basename($user->image ?? '');
        $newImage = pathinfo($url ?? '');

        if(!empty($url) && ($oldImageName != $newImage['basename'])) {
            $storage = \Storage::disk('users-images');

            if($user->image) {
                $storage->delete($user->image ?? '');
                $storage->delete(str_replace('.', '-thumb.', $user->image ?? ''));
            }

            $name = $newImage['filename'];
            $extension = $newImage['extension'];
            $path = date('Y/m/d/H/i');

            try {
                $storage->put("$path/$name.$extension", file_get_contents($url));

                $img = Image::make($storage->path("$path/$name.$extension"));
                $img->resize(300, 400);
                $img->save();

                $storage->put("$path/$name-thumb.$extension", file_get_contents($url));

                $img = Image::make($storage->path("$path/$name-thumb.$extension"));
                $img->resize(100, 100);
                $img->save();

                $user->update([
                    'image' => "$path/$name.$extension"
                ]);
            }
            catch(\Exception $e) {
                dd($e->getMessage());
            }
        }
    }

    public static function roles($userId = null) {
        $userId = $userId ? $userId : \Auth::user()->id;

        if(!isset(self::$cache[$userId]['roles'])) {
            self::$cache[$userId]['roles'] = UserRole::where('user_id', $userId)
                ->pluck('role_id')
                ->toArray();
        }

        return self::$cache[$userId]['roles'];
    }

    public static function makeWithLdapData($username, $password, $data) {
        $usersCount = User::withTrashed()
            ->where('username', $username)
            ->orWhere('mobile', $data->mobile)
            ->count();
        
        if($usersCount > 1) {
            return false;
        }

        $user = User::withTrashed()->updateOrCreate([
            'mobile' => $data->mobile,
        ], [
            'username' => $username,
            'first_name' => convert_characters($data->name),
            'last_name' => convert_characters($data->family),
            'gender' => User::GENDER_MALE,
            'status' => User::STATUS_ACTIVE,
            'type' => User::TYPE_USER,
            'email' => $username . '@nahad.ir',
            'password' => bcrypt($password),
            'ldap_login' => true,
            'deleted_at' => null
        ]);

        $info = LDAPService::call('infoByNationalCode', [
            'national_code' => $data->code_melli
        ]);

        $organizationalPost = OrganizationalPost::with('organizationPostRoles')
            ->where('code', $info->post_id ?? null) 
            ->first();

        if($organizationalPost->organizationPostRoles->count() > 0) {
            UserRole::upsert($organizationalPost->organizationPostRoles->map(function($organizationalRole) use ($user) {
                return [
                    'user_id' => $user->id,
                    'role_id' => $organizationalRole->role_id
                ];
            })->toArray(), [
                'user_id', 'role_id'
            ], [
                'user_id', 'role_id'
            ]);
        }

        UserCreated::dispatch($user);

        return $user;
    }

    public static function makeWithAuthServiceData($username, $password, $mobile, $data) {
        $usersCount = User::withTrashed()
            ->where('username', $username)
            ->orWhere('mobile', $mobile)
            ->count();
        
        if($usersCount > 1) {
            return false;
        }

        $user = User::withTrashed()->updateOrCreate([
            'mobile' => $mobile,
        ], [
            'username' => $username,
            'first_name' => convert_characters($data->first_name),
            'last_name' => convert_characters($data->last_name),
            'gender' => User::GENDER_MALE,
            'status' => User::STATUS_ACTIVE,
            'type' => User::TYPE_USER,
            'email' => $username . '@nahad.ir',
            'password' => bcrypt($password),
            'ldap_login' => true,
            'deleted_at' => null
        ]);

        $organizationPostCode = AuthService::organizationPostCode($data->user_network_code);

        $organizationalPost = OrganizationalPost::with('organizationPostRoles')
            ->where('code', $organizationPostCode ?? null) 
            ->first();

        if($organizationalPost && ($organizationalPost->organizationPostRoles->count() > 0)) {
            UserRole::upsert($organizationalPost->organizationPostRoles->map(function($organizationalRole) use ($user) {
                return [
                    'user_id' => $user->id,
                    'role_id' => $organizationalRole->role_id
                ];
            })->toArray(), [
                'user_id', 'role_id'
            ], [
                'user_id', 'role_id'
            ]);
        }

        UserCreated::dispatch($user);

        return $user;
    }

    public static function getGuestId() {
        if(\Auth::check()) {
            return null;
        }
        
        if(!self::$guestId) {
            self::$guestId = \Cookie::get('guest_id');
    
            if(!self::$guestId) {
                self::$guestId = \Str::random(32);
            }
            
            \Cookie::queue('guest_id', self::$guestId, config('auth.guest_id_expiration'));
        }
        
        return self::$guestId;
    }
}