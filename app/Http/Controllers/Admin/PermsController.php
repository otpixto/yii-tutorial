<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermsController extends BaseController
{

    public function index ()
    {

        Title::add( 'Права' );

        $perms = Permission::paginate( 30 );

        return view('admin.perms.index' )
            ->with( 'perms', $perms );

    }

    public function create ()
    {

        Title::add( 'Создать права' );

        $roles = Role::orderBy( 'name' )->get();

        return view('admin.perms.create' )
            ->with( 'roles', $roles );

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
            ->with( 'perm', $perm );

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

        $this->validate( $request, [
                'name' => [
                    'required',
                    'max:50',
                    Rule::unique( 'permissions' )->ignore( $perm->id )
                ]
            ]
        );

        $perm->fill( \Input::all() );
        $perm->save();

        return redirect()->route( 'perms.edit', $perm->id )
            ->with( 'success', 'Роль успешно отредактирована' );

    }

    public function store ( Request $request )
    {

        $this->validate( $request, [
                'name' => [
                    'required',
                    'max:50',
                    Rule::unique( 'permissions' )
                ],
                'roles' => 'array',
            ]
        );

        $perm = Permission::create( \Input::all() );

        if ( !empty( $request['roles'] ) )
        {
            foreach ( $request['roles'] as $role_name )
            {
                $role = Role::findByName( $role_name );
                $role->givePermissionTo( $perm->name );
            }
        }

        return redirect()->route( 'perms.edit', $perm->id )
            ->with( 'success', 'Права успешно добавлены' );

    }

}
