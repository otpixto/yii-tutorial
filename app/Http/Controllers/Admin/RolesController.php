<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Iphome\Permission\Models\Permission;
use Iphome\Permission\Models\Role;

class RolesController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Роли' );
    }

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );
        $guard = $request->get( 'guard', config( 'auth.defaults.guard' ) );

        $roles = Role
            ::where( 'guard', '=', $guard )
            ->orderBy( 'code' );

        if ( !empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $roles
                ->where( function ( $q ) use ( $s )
                {
                    return $q
                        ->where( 'code', 'like', $s )
                        ->orWhere( 'name', 'like', $s );
                });
        }

        $roles = $roles
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $log = Log::create([
            'text' => 'Просмотрел список ролей (стр.' . $request->get( 'page', 1 ) . ')'
        ]);
        $log->save();

        return view('admin.roles.index' )
            ->with( 'roles', $roles )
            ->with( 'guard', $guard )
            ->with( 'guards', $this->getGuards() );

    }

    public function show ( $id )
    {
        return redirect()->route( 'roles.index' );
    }

    public function edit ( $id )
    {

        Title::add( 'Редактировать роль' );

        $role = Role::find( $id );

        if ( ! $role )
        {
            return redirect()->route( 'roles.index' )
                ->withErrors( [ 'Роль не найдена' ] );
        }

        return view('admin.roles.edit' )
            ->with( 'role', $role )
            ->with( 'guards', $this->getGuards() );

    }

    public function create ()
    {

        Title::add( 'Создать роль' );

        return view('admin.roles.create' )
            ->with( 'guards', $this->getGuards() );

    }

    public function update ( Request $request, $id )
    {

        $role = Role::find( $id );

        if ( ! $role )
        {
            return redirect()->route( 'roles.index' )
                ->withErrors( [ 'Роль не найдена' ] );
        }

        $this->validate( $request, Role::getRules( $role->code ) );

        $res = $role->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()
                ->back()
                ->withErrors( $res );
        }

        return redirect()->route( 'roles.edit', $role->id )
            ->with( 'success', 'Роль успешно отредактирована' );

    }

    public function perms ( $id )
    {

        Title::add( 'Права доступа' );

        $role = Role::find( $id );

        if ( ! $role )
        {
            return redirect()->route( 'roles.index' )
                ->withErrors( [ 'Роль не найдена' ] );
        }

        $perms_tree = Permission::getTree( $role->guard );

        return view('admin.roles.perms' )
            ->with( 'role', $role )
            ->with( 'perms_tree', $perms_tree );

    }

    public function updatePerms ( Request $request, $id )
    {

        $role = Role::find( $id );

        if ( ! $role )
        {
            return redirect()->route( 'roles.index' )
                ->withErrors( [ 'Роль не найдена' ] );
        }

        $role->syncPermissions( $request->get( 'perms', [] ) );

        $this->clearCache( 'users' );

        return redirect()->route( 'roles.perms', $role->id )
            ->with( 'success', 'Права успешно отредактированы' );

    }

    public function store ( Request $request )
    {

        $this->validate( $request, Role::getRules() );

        $role = Role::create( $request->all() );
        $role->syncPermissions( $request->get( 'perms', [] ) );

        return redirect()->route( 'roles.index' )
            ->with( 'success', 'Роль успешно добавлена' );

    }

}
