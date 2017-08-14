<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use Illuminate\Http\Request;
use Iphome\Permission\Models\Permission;
use Iphome\Permission\Models\Role;

class RolesController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Роли' );
    }

    public function index ()
    {

        $search = trim( \Input::get( 'search', '' ) );

        $roles = Role
            ::orderBy( 'code' );

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

        $roles = $roles->paginate( 30 );

        return view('admin.roles.index' )
            ->with( 'roles', $roles );

    }

    public function show ( $id )
    {
        return redirect()->route( 'roles.index' );
    }

    public function edit ( $id )
    {

        Title::add( 'Редактировать роль' );

        $role = Role::find( $id );

        if ( !$role )
        {
            return redirect()->route( 'roles.index' )
                ->withErrors( [ 'Роль не найдена' ] );
        }

        $perms_tree = Permission::getTree();

        return view('admin.roles.edit' )
            ->with( 'role', $role )
            ->with( 'perms_tree', $perms_tree )
            ->with( 'guards', $this->getGuards() );

    }

    public function create ()
    {

        Title::add( 'Создать роль' );

        $perms_tree = Permission::getTree();

        return view('admin.roles.create' )
            ->with( 'perms_tree', $perms_tree )
            ->with( 'guards', $this->getGuards() );

    }

    public function update ( Request $request, $id )
    {

        $role = Role::find( $id );

        if ( !$role )
        {
            return redirect()->route( 'roles.index' )
                ->withErrors( [ 'Роль не найдена' ] );
        }

        $this->validate( $request, Role::getRules( $role->code ) );

        $role->fill( $request->all() );
        $role->save();

        $role->syncPermissions( $request->get( 'perms', [] ) );

        return redirect()->route( 'roles.edit', $role->id )
            ->with( 'success', 'Роль успешно отредактирована' );

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
