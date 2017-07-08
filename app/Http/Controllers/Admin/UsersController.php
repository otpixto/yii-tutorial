<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsersController extends BaseController
{

    public function index ()
    {

        Title::add( 'Пользователи' );

        $search = trim( \Input::get( 'search', '' ) );
        $role = trim( \Input::get( 'role', '' ) );

        $users = User::paginate( 30 );
        $roles = Role::orderBy( 'name' )->get();

        return view('admin.users.index' )
            ->with( 'users', $users )
            ->with( 'roles', $roles )
            ->with( 'role', $role )
            ->with( 'search', $search );

    }

    public function create ()
    {

        Title::add( 'Создать пользователя' );

        $roles = Role::orderBy( 'name' )->get();
        $perms = Permission::orderBy( 'name' )->get();

        return view('admin.users.create' )
            ->with( 'perms', $perms )
            ->with( 'roles', $roles );

    }

    public function edit ( $id )
    {

        Title::add( 'Редактировать пользователя' );

        $user = User::find( $id );

        if ( !$user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $roles = Role::orderBy( 'name' )->get();
        $perms = Permission::orderBy( 'name' )->get();

        return view('admin.users.edit' )
            ->with( 'user', $user )
            ->with( 'perms', $perms )
            ->with( 'roles', $roles );

    }

    public function update ( Request $request, $id )
    {

        $user = User::find( $id );

        if ( !$user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        switch ( \Input::get( 'action' ) )
        {
            case 'edit_personal':
                $res = $user->edit( \Input::all() );
                if ( $res instanceof MessageBag )
                {
                    return redirect()->back()->withInput()->withErrors( $res );
                }
                break;
            case 'change_password':
                $res = $user->changePass( \Input::all() );
                if ( $res instanceof MessageBag )
                {
                    return redirect()->back()->withInput()->withErrors( $res );
                }
                break;
            case 'edit_access':

                break;
            default:
                return redirect()->back()->withInput()->withErrors( [ 'Некорректное действие' ] );
                break;
        }

        return redirect()->route( 'users.index' )
            ->with( 'success', 'Пользователь успешно отредактирован' );

    }

}
