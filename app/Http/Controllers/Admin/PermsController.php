<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use Illuminate\Http\Request;
use Iphome\Permission\Models\Permission;
use Iphome\Permission\Models\Role;

class PermsController extends BaseController
{

    public function index ()
    {

        Title::add( 'Права' );

        $search = trim( \Input::get( 'search', '' ) );

        if ( !empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', $search ) . '%';
            $perms = Permission
                ::where( function ( $q ) use ( $s )
                {
                    return $q
                        ->where( 'code', 'like', $s )
                        ->orWhere( 'name', 'like', $s )
                        ->orWhere( 'guard_name', 'like', $s );
                })
                ->orderBy( 'code' )
                ->orderBy( 'name' )
                ->paginate( 30 );
        }
        else
        {
            $perms_tree = Permission::getTree();
        }

        return view('admin.perms.index' )
            ->with( 'perms', $perms ?? null )
            ->with( 'perms_tree', $perms_tree ?? null );

    }

    public function create ()
    {

        Title::add( 'Создать права' );

        $roles = Role
            ::orderBy( 'name' )
            ->get();

        return view('admin.perms.create' )
            ->with( 'roles', $roles )
            ->with( 'guards', $this->getGuards() );

    }

    public function edit ( $id )
    {

        Title::add( 'Редактировать права' );

        $perm = Permission::find( $id );

        if ( !$perm )
        {
            return redirect()->route( 'admin.permissions' )
                ->withErrors( [ 'Права не найдены' ] );
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

        if ( !$perm )
        {
            return redirect()->route( 'perms.index' )
                ->withErrors( [ 'Права не найдены' ] );
        }

        $this->validate( $request, Permission::getRules( $perm->code ) );

        $perm->fill( $request->all() );
        $perm->save();

        return redirect()->route( 'perms.edit', $perm->id )
            ->with( 'success', 'Роль успешно отредактирована' );

    }

    public function store ( Request $request )
    {

        $this->validate( $request, Permission::getRules() );

        $perm = Permission::create( $request->all() );

        if ( !empty( $request['roles'] ) )
        {
            foreach ( $request['roles'] as $code )
            {
                $role = Role::findByCode( $code );
                $role->givePermissionTo( $perm->code );
            }
        }

        return redirect()->route( 'perms.edit', $perm->id )
            ->with( 'success', 'Права успешно добавлены' );

    }

}
