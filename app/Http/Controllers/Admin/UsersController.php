<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Models\Management;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Iphome\Permission\Models\Permission;
use Iphome\Permission\Models\Role;

class UsersController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Пользователи' );
    }

    public function index ()
    {

        $search = trim( \Input::get( 'search', '' ) );
        $role = trim( \Input::get( 'role', '' ) );

        if ( !empty( $role ) )
        {
            $users = User::role( $role )->orderBy( 'id', 'desc' );
        }
        else
        {
            $users = User::orderBy( 'id', 'desc' );
        }

        if ( !empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', $search ) . '%';
            $users
                ->where( function ( $q ) use ( $s )
                {
                    return $q
                        ->where( 'firstname', 'like', $s )
                        ->orWhere( 'middlename', 'like', $s )
                        ->orWhere( 'lastname', 'like', $s )
                        ->orWhere( 'email', 'like', $s )
                        ->orWhere( 'phone', 'like', $s );
                });
        }

        $users = $users->paginate( 30 );

        //$user = $users->first();
        //$perms = $user->getAllPermissions();
        //dd( $user, $perms );

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
        $perms_tree = Permission::getTree();

        return view('admin.users.create' )
            ->with( 'perms_tree', $perms_tree )
            ->with( 'roles', $roles );

    }

    public function store ( Request $request )
    {
        dd( $request->all() );
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
        $perms_tree = Permission::getTree();

        $managements = Management::orderBy( 'name' )->get()->pluck( 'name', 'id' );

        return view('admin.users.edit' )
            ->with( 'user', $user )
            ->with( 'perms_tree', $perms_tree )
            ->with( 'roles', $roles )
            ->with( 'managements', $managements );

    }

    public function update ( Request $request, $id )
    {

        $user = User::find( $id );

        if ( !$user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        switch ( $request->get( 'action' ) )
        {
            case 'edit_personal':
                $res = $user->edit( \Input::all() );
                if ( $res instanceof MessageBag )
                {
                    return redirect()->back()->withInput()->withErrors( $res );
                }
                break;
            case 'edit_binds':
                $user->management_id = $request->get( 'management_id' ) ?? null;
                $user->save();
                break;
            case 'change_password':
                $res = $user->changePass( \Input::all() );
                if ( $res instanceof MessageBag )
                {
                    return redirect()->back()->withInput()->withErrors( $res );
                }
                break;
            case 'edit_access':

                $user->syncRoles( $request->get( 'roles', [] ) );
                $user->syncPermissions( $request->get( 'perms', [] ) );

                $user->active = $request->get( 'active', 0 );
                $user->save();

                break;
            default:
                return redirect()->back()->withInput()->withErrors( [ 'Некорректное действие' ] );
                break;
        }

        return redirect()->route( 'users.edit', $user->id )
            ->with( 'success', 'Пользователь успешно отредактирован' );

    }

}
