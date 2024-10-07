<?php

namespace Nhd\Foundation\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Nhd\Foundation\Http\Requests\Dashboard\Settings\DefaultsRequest;
use Nhd\Foundation\Models\OrganizationalPost;
use Nhd\Foundation\Models\Role;
use Nhd\Foundation\Models\Settings;
use Nhd\Foundation\Models\Option;
use Nhd\Foundation\Support\Alert;

class SettingsController extends Controller {
    public function defaultsGet() {
        $this->authorize('defaults', Settings::class);

        $options = Option::getAll([
            'two_step_login_client',
            'two_step_login_sms',
        ]);

        $roles = Role::all();

        $organizationlPosts = OrganizationalPost::with('organizationPostRoles')
            ->get();

        return view('auth::dashboard.settings.defaults', [
            'roles' => $roles,
            'options' => $options,
            'organization_posts' => $organizationlPosts
        ]);
    }

    public function defaultsPost(DefaultsRequest $request) {
        $this->authorize('defaults', Settings::class);

        Option::set('two_step_login_client', $request->boolean('two_step_login_client'));
        Option::set('two_step_login_sms', $request->get('two_step_login_sms'));

        $this->saveLdapData($request);

        Alert::add('تنظیمات با موفقیت اعمال گردید', Alert::SUCCESS);

        return redirect()->back();
    }

    private function saveLdapData($request) {
        $organizationalPosts = OrganizationalPost::all();

        foreach($organizationalPosts as $organizationalPost) {
            $organizationalPost->roles()->sync($request->input("organizational_post_roles.$organizationalPost->id", []));
        }
    }
}