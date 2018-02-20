<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Models\Management;
use App\Models\Region;
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

    public function loginas ( Request $request, $id )
    {
        if ( ! \Auth::user()->admin )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'У вас недостаточно прав' ] );
        }
        $user = User::find( $id );
        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }
        \Auth::login( $user );
        return redirect()->route( 'home' );
    }

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );
        $role = trim( $request->get( 'role', '' ) );
        $region = trim( $request->get( 'region', '' ) );

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

        if ( ! empty( $region ) )
        {
            $users
                ->whereHas( 'regions', function ( $q ) use ( $region )
                {
                    return $q
                        ->mine()
                        ->where( $q->getModel()->getTable() . '.id', '=', $region );
                });
        }
        else if ( ! Region::isOperatorUrl() || ! \Auth::user()->can( 'supervisor.all_regions' ) )
        {
            $users
                ->whereHas( 'regions', function ( $q )
                {
                    return $q
                        ->mine();
                });
        }

        $users = $users
            ->paginate( 30 )
            ->appends( $request->all() );

        $roles = Role::orderBy( 'name' )->get();

        $regions = Region
            ::mine()
            ->orderBy( 'name' )
            ->get();

        return view('admin.users.index' )
            ->with( 'users', $users )
            ->with( 'roles', $roles )
            ->with( 'role', $role )
            ->with( 'regions', $regions )
            ->with( 'search', $search );

    }

    public function create ()
    {

        Title::add( 'Создать пользователя' );

        $roles = Role::orderBy( 'name' )->get();

        $regions = Region
            ::mine()
            ->orderBy( 'name' )
            ->get();

        return view('admin.users.create' )
            ->with( 'roles', $roles )
            ->with( 'regions', $regions );

    }

    public function store ( Request $request )
    {

        $this->validate( $request, User::$rules_create );

        $user = User::create( $request->all() );

        if ( $user instanceof MessageBag )
        {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors( $user );
        }

        return redirect()->route( 'users.edit', $user->id )
            ->with( 'success', 'Пользователь успешно создан' );

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

        $managements = Management
            ::whereNotIn( 'id', $user->managements->pluck( 'id' ) )
            ->orderBy( 'name' )
            ->get()
            ->pluck( 'name', 'id' );

        $regions = Region
            ::mine()
            ->orderBy( 'name' )
            ->get();

        return view('admin.users.edit' )
            ->with( 'user', $user )
            ->with( 'perms_tree', $perms_tree )
            ->with( 'roles', $roles )
            ->with( 'managements', $managements )
            ->with( 'regions', $regions );

    }

    public function update ( Request $request, $id )
    {

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()
                ->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        switch ( $request->get( 'action' ) )
        {
            case 'edit_personal':
                $this->validate( $request, User::$rules_edit );
                $res = $user->edit( $request->all() );
                if ( $res instanceof MessageBag )
                {
                    return redirect()->back()->withInput()->withErrors( $res );
                }
                break;
            case 'edit_binds':
                $user->managements()->sync( $request->get( 'managements' ) );
                break;
            case 'change_password':
                $this->validate( $request, User::$rules_password );
                $res = $user->changePass( $request->all() );
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
                \Cache::tags( 'users' )->forget( 'user.availableStatuses.' . $user->id );
                break;
            default:
                return redirect()->back()->withInput()->withErrors( [ 'Некорректное действие' ] );
                break;
        }

        return redirect()->route( 'users.edit', $user->id )
            ->with( 'success', 'Пользователь успешно отредактирован' );

    }

}
