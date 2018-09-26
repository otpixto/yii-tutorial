<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Models\Log;
use App\Models\Management;
use App\Models\Provider;
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

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );
        $role = trim( $request->get( 'role', '' ) );

        if ( ! empty( $role ) )
        {
            $users = User::role( $role )->mine()->orderBy( 'id', 'desc' );
        }
        else
        {
            $users = User::mine()->orderBy( 'id', 'desc' );
        }

        if ( ! empty( $search ) )
        {
            $users
                ->search( $search );
        }

        $users = $users
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $roles = Role
            ::mine()
            ->orderBy( 'name' )
            ->get();

        $log = Log::create([
            'text' => 'Просмотрел список пользователей стр.' . $request->get( 'page', 1 )
        ]);
        $log->save();

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

        $providers = Provider
            ::mine()
            ->orderBy( 'name' )
            ->get();

        return view('admin.users.create' )
            ->with( 'roles', $roles )
            ->with( 'providers', $providers );

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

        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        return view('admin.users.edit' )
            ->with( 'user', $user );

    }

    public function perms ( $id )
    {

        Title::add( 'Редактировать пользователя' );

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $roles = Role::orderBy( 'name' )->get();

        $perms_tree = Permission::getTree();

        return view('admin.users.perms' )
            ->with( 'user', $user )
            ->with( 'roles', $roles )
            ->with( 'perms_tree', $perms_tree );

    }

    public function permsUpdate ( Request $request, $id )
    {

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $user->syncPermissions( $request->get( 'perms', [] ) );

        $this->clearCache();

    }

    public function rolesUpdate ( Request $request, $id )
    {

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $user->syncRoles( $request->get( 'roles', [] ) );

        $this->clearCache();

        $perms_tree = Permission::getTree();

        return view('admin.perms.tree' )
            ->with( 'user', $user )
            ->with( 'perms_tree', $perms_tree );

    }

    public function providers ( $id )
    {

        Title::add( 'Редактировать пользователя' );

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $userProviders = $user->providers()
            ->paginate( 30 );

        $providers = Provider
            ::whereNotIn( Provider::$_table . '.id', $user->providers()->pluck( Provider::$_table . '.id' ) )
            ->pluck( 'name', 'id' );

        return view('admin.users.providers' )
            ->with( 'user', $user )
            ->with( 'userProviders', $userProviders )
            ->with( 'providers', $providers );

    }

    public function providersAdd ( Request $request, $id )
    {

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $user->providers()->attach( $request->get( 'providers' ) );

        return redirect()->route( 'users.providers', $user->id )
            ->with( 'success', 'Привязка прошла успешно' );

    }

    public function providersDel ( Request $request, $id )
    {

        $rules = [
            'provider_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $user->providers()->detach( $request->get( 'provider_id' ) );

    }

    public function managements ( Request $request, $id )
    {

        Title::add( 'Редактировать пользователя' );

        $user = User::find( $id );
        $search = trim( $request->get( 'search', '' ) );

        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $userManagements = $user->managements()
            ->orderBy( Management::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', $search ) . '%';
            $userManagements
                ->where( Management::$_table . '.name', 'like', $s );
        }

        $userManagements = $userManagements
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $availableManagements = Management
            ::mine( Management::IGNORE_MANAGEMENT )
            ->whereNotIn( Management::$_table . '.id', $user->managements()->pluck( Management::$_table . '.id' ) )
            ->orderBy( Management::$_table . '.name' )
            ->get();

        $res = [];
        foreach ( $availableManagements as $availableManagement )
        {
            $res[ $availableManagement->parent->name ?? 'Без родителя' ][ $availableManagement->id ] = $availableManagement->name;
        }

        ksort( $res );
        $availableManagements = $res;

        return view('admin.users.managements' )
            ->with( 'user', $user )
            ->with( 'userManagements', $userManagements )
            ->with( 'availableManagements', $availableManagements )
            ->with( 'search', $search );

    }

    public function managementsSearch ( Request $request, $id )
    {

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';

        $managements = Management
            ::mine()
            ->select(
                Management::$_table . '.id',
                Management::$_table . '.name AS text'
            )
            ->where( Management::$_table . '.name', 'like', $s )
            ->whereNotIn( Management::$_table . '.id', $user->managements()->pluck( Management::$_table . '.id' ) )
            ->orderBy( Management::$_table . '.name' )
            ->get();

        return $managements;

    }

    public function managementsAdd ( Request $request, $id )
    {

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $user->managements()->attach( $request->get( 'managements' ) );

        return redirect()->route( 'users.managements', $user->id )
            ->with( 'success', 'УО успешно привязаны' );

    }

    public function managementsDel ( Request $request, $id )
    {

        $rules = [
            'management_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $user->managements()->detach( $request->get( 'management_id' ) );

    }

    public function logs ( $id )
    {

        Title::add( 'Редактировать пользователя' );

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $userLogsIn = $user
            ->logs()
            ->orderBy( 'id', 'desc' )
            ->take( config( 'pagination.per_page' ) )
            ->get();

        $userLogsOut = Log
            ::where( 'author_id', '=', $user->id )
            ->orderBy( 'id', 'desc' )
            ->take( config( 'pagination.per_page' ) )
            ->get();

        return view('admin.users.logs' )
            ->with( 'user', $user )
            ->with( 'userLogsIn', $userLogsIn )
            ->with( 'userLogsOut', $userLogsOut );

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

        $rules = [
            'active' => [
                'boolean',
            ],
            'firstname' => [
                'required',
                'max:255',
            ],
            'middlename' => [
                'nullable',
                'max:255',
            ],
            'lastname' => [
                'required',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'max:18',
                'regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            ],
            'prefix' => [
                'nullable',
                'max:255',
            ],
        ];

        $this->validate( $request, $rules );
        $res = $user->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()->withInput()->withErrors( $res );
        }

        return redirect()->route( 'users.edit', $user->id )
            ->with( 'success', 'Пользователь успешно отредактирован' );

    }

    public function changePassword ( Request $request, $id )
    {

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()
                ->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $rules = [
            'password' => [
                'required',
                'min: 6',
                'confirmed'
            ]
        ];

        $this->validate( $request, $rules );

        $res = $user->changePass( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()->withInput()->withErrors( $res );
        }

        return redirect()->route( 'users.edit', $user->id )
            ->with( 'success', 'Пароль успешно изменен' );

    }

    public function uploadPhoto ( Request $request, $id )
    {

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()
                ->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $rules = [
            'image' => [
                'required',
                'image',
            ]
        ];

        $this->validate( $request, $rules );



        return redirect()->route( 'users.edit', $user->id )
            ->with( 'success', 'Фотография успешно загружена' );

    }

    public function search ( Request $request )
    {

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';
        $provider_id = $request->get( 'provider_id', Provider::getCurrent() ? Provider::$current->id : null );

        $res = User
            ::mine()
            ->select(
                User::$_table . '.id',
                \DB::raw( 'CONCAT_WS( \' \', ' . User::$_table . '.lastname, ' . User::$_table . '.firstname, ' . User::$_table . '.middlename ) AS fullname' )
            )
            ->where( 'active', '=', 1 )
            ->having( 'fullname', 'like', $s )
            ->orderBy( 'fullname' );

        if ( ! empty( $provider_id ) )
        {
            $res
                ->where( User::$_table . '.provider_id', '=', $provider_id );
        }

        $res = $res
            ->get();

        $users = [];
        foreach ( $res as $r )
        {
            $users[] = [
                'id' => $r->id,
                'text' => $r->fullname
            ];
        }

        return $users;

    }

}
