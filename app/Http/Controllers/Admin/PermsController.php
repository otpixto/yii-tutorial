<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Models\Log;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Iphome\Permission\Models\Permission;
use Iphome\Permission\Models\Role;

class PermsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Права доступа' );
    }

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );
        $guard = $request->get( 'guard', config( 'auth.defaults.guard' ) );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', $search ) . '%';
            $perms = Permission
                ::where( function ( $q ) use ( $s )
                {
                    return $q
                        ->where( 'code', 'like', $s )
                        ->orWhere( 'name', 'like', $s );
                })
                ->where( 'guard', '=', $guard )
                ->orderBy( 'code' )
                ->orderBy( 'name' )
                ->paginate( config( 'pagination.per_page' ) )
                ->appends( $request->all() );
            $log = Log::create([
                'text' => 'Просмотрел список прав (стр.' . $request->get( 'page', 1 ) . ')'
            ]);
            $log->save();
        }
        else
        {
            $log = Log::create([
                'text' => 'Просмотрел список прав (дерево)'
            ]);
            $log->save();
            $perms_tree = Permission::getTree( $guard );
        }

        return view('admin.perms.index' )
            ->with( 'perms', $perms ?? null )
            ->with( 'perms_tree', $perms_tree ?? null )
            ->with( 'guard', $guard )
            ->with( 'guards', $this->getGuards() );

    }

    public function create ()
    {

        Title::add( 'Создать права доступа' );

        $roles = Role
            ::orderBy( 'name' )
            ->get();

        return view('admin.perms.create' )
            ->with( 'roles', $roles )
            ->with( 'guards', $this->getGuards() );

    }

    public function edit ( $id )
    {

        Title::add( 'Редактировать права доступа' );

        $perm = Permission::find( $id );

        if ( ! $perm )
        {
            return redirect()->route( 'perms.index' )
                ->withErrors( [ 'Права доступа не найдены' ] );
        }

        return view('admin.perms.edit' )
            ->with( 'perm', $perm )
            ->with( 'guards', $this->getGuards() );

    }

    public function show ( $id )
    {
        return redirect()->route( 'perms.index' );
    }


    public function update ( Request $request, $id )
    {

        $perm = Permission::find( $id );

        if ( ! $perm )
        {
            return redirect()->route( 'perms.index' )
                ->withErrors( [ 'Права доступа не найдены' ] );
        }

        $this->validate( $request, Permission::getRules( $perm->code ) );

        $res = $perm->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()
                ->back()
                ->withErrors( $res );
        }

        return redirect()->route( 'perms.edit', $perm->id )
            ->with( 'success', 'Права доступа успешно отредактированы' );

    }

    public function users ( $id )
    {

        Title::add( 'Привязка прав доступа к пользователям' );

        $perm = Permission::find( $id );

        if ( ! $perm )
        {
            return redirect()->route( 'perms.index' )
                ->withErrors( [ 'Права доступа не найдены' ] );
        }

        $permUsers = $perm->users()->paginate( 30 );

        return view('admin.perms.users' )
            ->with( 'perm', $perm )
            ->with( 'permUsers', $permUsers );

    }

    public function usersSearch ( Request $request, $id )
    {

        $perm = Permission::find( $id );

        if ( ! $perm )
        {
            return redirect()->route( 'perms.index' )
                ->withErrors( [ 'Права доступа не найдены' ] );
        }

        $res = User
            ::mine()
            ->search( $request->get( 'q' ) )
            ->whereNotIn( User::$_table . '.id', $perm->users()->pluck( User::$_table . '.id' ) )
            ->get();

        $users = new Collection();
        foreach ( $res as $r )
        {
            $users->push([
                'id' => $r->id,
                'text' => $r->getName()
            ]);
        }

        $users = $users->sortBy( 'text' );

        return $users;

    }

    public function usersAdd ( Request $request, $id )
    {

        $perm = Permission::find( $id );

        if ( ! $perm )
        {
            return redirect()->route( 'perms.index' )
                ->withErrors( [ 'Права доступа не найдены' ] );
        }

        $perm->users()->attach( $request->get( 'users' ) );

        return redirect()->route( 'perms.users', $perm->id )
            ->with( 'success', 'Привязка прошла успешно' );

    }

    public function usersDel ( Request $request, $id )
    {

        $rules = [
            'user_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $perm = Permission::find( $id );

        if ( ! $perm )
        {
            return redirect()->route( 'perms.index' )
                ->withErrors( [ 'Права доступа не найдены' ] );
        }

        $perm->users()->detach( $request->get( 'user_id' ) );

    }

    public function roles ( $id )
    {

        Title::add( 'Привязка прав доступа к ролям' );

        $perm = Permission::find( $id );

        if ( ! $perm )
        {
            return redirect()->route( 'perms.index' )
                ->withErrors( [ 'Права доступа не найдены' ] );
        }

        $roles = Role
            ::where( 'guard', '=', $perm->guard )
            ->get();

        return view('admin.perms.roles' )
            ->with( 'perm', $perm )
            ->with( 'roles', $roles );

    }

    public function updateRoles ( Request $request, $id )
    {

        $perm = Permission::find( $id );

        if ( ! $perm )
        {
            return redirect()->route( 'perms.index' )
                ->withErrors( [ 'Права доступа не найдены' ] );
        }

        $roles = Role::all();
        $selected_roles = $request->get( 'selected_roles', [] );

        foreach ( $roles as $role )
        {
            if ( in_array( $role->id, $selected_roles ) )
            {
                if ( ! $perm->roles->contains( 'id', $role->id ) )
                {
                    $role->permissions()->attach( $id );
                }
            }
            else
            {
                $role->permissions()->detach( $id );
            }
        }

        $this->clearCache( 'users' );

        return redirect()->route( 'perms.roles', $perm->id )
            ->with( 'success', 'Права доступа успешно отредактированы' );

    }

    public function store ( Request $request )
    {

        $this->validate( $request, Permission::getRules() );

        $perm = Permission::create( $request->all() );

        if ( ! empty( $request[ 'roles' ] ) )
        {
            foreach ( $request[ 'roles' ] as $code )
            {
                $role = Role::findByCode( $code );
                $role->givePermissionTo( $perm->code );
            }
            $this->clearCache( 'users' );
        }

        return redirect()->route( 'perms.edit', $perm->id )
            ->with( 'success', 'Права успешно добавлены' );

    }

    public function searchUsers ( Request $request )
    {

        $perm_id = $request->get( 'perm_id' );

        $users = User
            ::search( $request->get( 'q' ) )
            ->whereDoesntHave( 'permissions', function ( $permissions ) use ( $perm_id )
            {
                return $permissions
                    ->where( 'id', '=', $perm_id );
            })
            ->get();

        $res = [];
        foreach ( $users as $user )
        {
            $res[] = [
                'id' => $user->id,
                'text' => $user->getName()
            ];
        }

        return $res;

    }

}
