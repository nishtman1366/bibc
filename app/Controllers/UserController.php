<?php

namespace App\Controllers;

use App\Controllers\Controller as BaseController;
use App\Models\AdminGroup;
use App\Models\Area;
use App\Models\User;

class UserController extends BaseController
{
    public function index()
    {
        $users = User::with('adminGroups')
            ->orderBy('iAdminId', 'ASC')
            ->get();
        return view('pages.users.list', compact('users'));
    }

    public function form(int $id = null)
    {
        $areas = Area::where('sActive', 'Yes')->orderBy('sAreaNamePersian', 'ASC')->get();
        $groups = AdminGroup::orderBy('iGroupId', 'ASC')->get();
        $user = null;
        if (!is_null($id)) {
            $user = User::where('iAdminId', $id)->get()->first();
        }
        return view('pages.users.form', compact('areas', 'groups', 'user'));
    }

    public function create()
    {
        //TODO validation for create user
        User::create(input()->all());
        return redirect(url('users'));
    }

    public function update(int $id)
    {
        //TODO validation for edit user
        $user = User::find($id);
        $user->fill(input()->all());
        $user->save();

        return redirect(url('users'));
    }

    public function delete(int $id)
    {
        if (!is_null($id)) {
//            User::destroy($id);
            User::find($id)->update(['eStatus' => 'Deleted']);
        }
        return redirect(url('users'));
    }
}