<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends BaseController
{

    public function index ()
    {

        Title::add( 'Роли' );

        $roles = Role::orderBy( 'name' )->paginate( 30 );

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

        $perms = Permission::orderBy( 'name' )->get();

        return view('admin.roles.edit' )
            ->with( 'role', $role )
            ->with( 'perms', $perms );

    }

    public function update ( Request $request, $id )
    {

        $role = Role::find( $id );

        if ( !$role )
        {
            return redirect()->route( 'roles.index' )
                ->withErrors( [ 'Роль не найдена' ] );
        }

        $this->validate( $request, [
                'name' => [
                    'required',
                    'max:50',
                    Rule::unique( 'roles' )->ignore( $role->id )
                ],
                'perms' => 'array',
            ]
        );

        $role->fill( \Input::all() );
        $role->save();

        $role->syncPermissions( $request['perms'] ?? [] );

        return redirect()->route( 'roles.edit', $role->id )
            ->with( 'success', 'Роль успешно отредактирована' );

    }

    public function create ()
    {

        Title::add( 'Создать роль' );

        $perms = Permission::orderBy( 'name' )->get();

        return view('admin.roles.create' )
            ->with( 'perms', $perms );

    }

    public function store ( Request $request )
    {

        $this->validate( $request, [
                'name' => [
                    'required',
                    'max:50',
                    Rule::unique( 'roles' )
                ],
                'perms' => 'array',
            ]
        );

        $role = Role::create( \Input::all() );
        $role->syncPermissions( $request['perms'] ?? [] );

        return redirect()->route( 'roles.index' )
            ->with( 'success', 'Роль успешно добавлена' );

    }

}
