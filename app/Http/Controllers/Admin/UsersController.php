<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Models\Log;
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
		if ( ! \Auth::user()->admin && ! \Auth::user()->can( 'admin.loginas' ) )
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
		if ( ! $user->can( 'supervisor.all_regions' ) )
		{
			if ( ! $user->regions->count() )
			{
				return redirect()->route( 'users.index' )
					->withErrors( [ 'У пользователя нет привязанных регионов' ] );
			}
			$redirect = ( \Config::get( 'app.ssl' ) ? 'https://' : 'http://' ) . $user->regions->first()->domain;
		}
		else
		{
			$redirect = route( 'home' );
		}
		\Auth::login( $user );
		return redirect()->to( $redirect );
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
            $users
                ->search( $search );
        }

        if ( ! empty( $region ) )
        {
            $users
                ->whereHas( 'regions', function ( $q ) use ( $region )
                {
                    return $q
                        ->mine()
                        ->where( Region::$_table . '.id', '=', $region );
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

    public function regions ( $id )
    {

        Title::add( 'Редактировать пользователя' );

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $userRegions = $user->regions()
            ->paginate( 30 );

        $regions = Region
            ::whereNotIn( Region::$_table . '.id', $user->regions()->pluck( Region::$_table . '.id' ) )
            ->pluck( 'name', 'id' );

        return view('admin.users.regions' )
            ->with( 'user', $user )
            ->with( 'userRegions', $userRegions )
            ->with( 'regions', $regions );

    }

    public function regionsAdd ( Request $request, $id )
    {

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $user->regions()->attach( $request->get( 'regions' ) );

        return redirect()->route( 'users.regions', $user->id )
            ->with( 'success', 'Привязка прошла успешно' );

    }

    public function regionsDel ( Request $request, $id )
    {

        $rules = [
            'region_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $user->regions()->detach( $request->get( 'region_id' ) );

    }

    public function managements ( $id )
    {

        Title::add( 'Редактировать пользователя' );

        $user = User::find( $id );

        if ( ! $user )
        {
            return redirect()->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }

        $userManagements = $user->managements()
            ->orderBy( 'name' )
            ->paginate( 30 );

        return view('admin.users.managements' )
            ->with( 'user', $user )
            ->with( 'userManagements', $userManagements );

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
            ->where( 'text', 'not like', '%авторизовался%' )
            ->where( 'text', 'not like', '%выход%' )
            ->orderBy( 'id', 'desc' )
            ->take( 30 )
            ->get();

        $userLogsOut = Log
            ::where( 'author_id', '=', $user->id )
            ->orderBy( 'id', 'desc' )
            ->take( 30 )
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

}
