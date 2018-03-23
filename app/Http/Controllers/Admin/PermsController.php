<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use Illuminate\Http\Request;
use Iphome\Permission\Models\Permission;
use Iphome\Permission\Models\Role;

class PermsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Права' );
    }

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );
        $guard = $request->get( 'guard', config( 'auth.defaults.guard' ) );

        if ( !empty( $search ) )
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
                ->paginate( 30 )
                ->appends( $request->all() );
        }
        else
        {
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

        if ( ! $perm )
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

        if ( ! $perm )
        {
            return redirect()->route( 'perms.index' )
                ->withErrors( [ 'Права не найдены' ] );
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
            ->with( 'success', 'Права успешно отредактированы' );

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

}
