<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Models\Building;
use App\Models\Management;
use App\Models\Provider;
use App\Models\ProviderKey;
use App\Models\ProviderToken;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Storage;

class ProvidersController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Поставщики' );
        ProviderToken
            ::join( 'providers_keys', 'providers_keys.id', '=', 'providers_tokens.provider_key_id' )
            ->whereRaw( '( TIME_TO_SEC( TIMEDIFF( CURRENT_TIMESTAMP, providers_tokens.updated_at ) ) / 60 ) >= providers_keys.token_life' )
            ->delete();
    }

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );
        $building_id = $request->get( 'building_id', null );

        $providers = Provider
            ::orderBy( Provider::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $providers
                ->where( Provider::$_table . '.name', 'like', $s );
        }

        if ( ! empty( $building_id ) )
        {
            $providers
                ->whereHas( 'buildings', function ( $q ) use ( $building_id )
                {
                    return $q
                        ->where( Building::$_table . '.id', '=', $building_id );
                });
        }

        $providers = $providers
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $this->addLog( 'Просмотрел список провайдеров (стр.' . $request->get( 'page', 1 ) . ')' );

        return view('admin.providers.index' )
            ->with( 'providers', $providers );

    }

    public function show ( $id )
    {
        return $this->edit( $id );
    }

    public function edit ( $id )
    {

        Title::add( 'Редактировать поставщика' );

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        return view('admin.providers.edit' )
            ->with( 'provider', $provider );

    }

    public function phonesCreate ( Request $request, $provider_id )
    {
        Title::add( 'Добавить телефон' );
        $provider = Provider::find( $provider_id );
        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }
        return view('admin.providers.create_phone' )
            ->with( 'provider', $provider );
    }

    public function phonesStore ( Request $request, $provider_id )
    {
        $provider = Provider::find( $provider_id );
        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }
        $rules = [
            'phone'                 => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
            'name'                  => 'required|max:100',
            'description'           => 'nullable',
        ];
        $this->validate( $request, $rules );
        $phone = $provider->addPhone( $request->all() );
        if ( $phone instanceof MessageBag )
        {
            return redirect()->back()
                ->withInput()
                ->withErrors( $phone );
        }
        return redirect()->route( 'providers.edit', $provider->id )
            ->with( 'success', 'Номер успешно добавлен' );

    }

    public function phonesEdit ( Request $request, $provider_id, $phone_id )
    {
        Title::add( 'Редактировать телефон' );
        $provider = Provider::find( $provider_id );
        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }
        $phone = $provider->phones()->find( $phone_id );
        if ( ! $phone )
        {
            return redirect()->route( 'providers.edit', $provider->id )
                ->withErrors( [ 'Телефон не найден' ] );
        }
        return view('admin.providers.edit_phone' )
            ->with( 'provider', $provider )
            ->with( 'phone', $phone );
    }

    public function phonesUpdate ( Request $request, $provider_id, $phone_id )
    {
        $provider = Provider::find( $provider_id );
        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }
        $phone = $provider->phones()->find( $phone_id );
        if ( ! $phone )
        {
            return redirect()->route( 'providers.edit', $provider->id )
                ->withErrors( [ 'Телефон не найден' ] );
        }
        $rules = [
            'phone'                 => 'required|unique:providers_phones,phone,' . $phone->id . ',id',
            'name'                  => 'required',
            'description'           => 'nullable',
        ];
        $this->validate( $request, $rules );
        $phone->edit( $request->all() );
        return redirect()->route( 'providers.edit', $provider->id )
            ->with( 'success', 'Номер успешно отредактирован' );

    }

    public function phonesDel ( Request $request, $id )
    {

        $rules = [
            'phone_id'                 => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $provider->phones()->find( $request->get( 'phone_id' ) )->delete();

    }

    public function keysCreate ( Request $request, $provider_id )
    {
        Title::add( 'Добавить ключ' );
        $provider = Provider::find( $provider_id );
        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }
        return view('admin.providers.create_key' )
            ->with( 'provider', $provider );
    }

    public function keysStore ( Request $request, $provider_id )
    {
        $provider = Provider::find( $provider_id );
        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }
        $rules = [
            'description'           => 'nullable',
            'ip'                    => 'nullable',
            'referer'               => 'nullable',
            'token_life'            => 'integer|min:1',
        ];
        $this->validate( $request, $rules );
        $providerKey = $provider->addKey( $request->all() );
        if ( $providerKey instanceof MessageBag )
        {
            return redirect()->back()
                ->withInput()
                ->withErrors( $providerKey );
        }
        return redirect()->route( 'providers.keys.edit', [ $provider->id, $providerKey->id ] )
            ->with( 'success', 'Ключ успешно создан' );

    }

    public function keysEdit ( Request $request, $provider_id, $key_id )
    {
        Title::add( 'Редактировать ключ' );
        $provider = Provider::find( $provider_id );
        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }
        $providerKey = $provider->providerKeys()->find( $key_id );
        if ( ! $providerKey )
        {
            return redirect()->route( 'providers.edit', $provider->id )
                ->withErrors( [ 'Ключ не найден' ] );
        }
        return view('admin.providers.edit_key' )
            ->with( 'provider', $provider )
            ->with( 'providerKey', $providerKey );
    }

    public function keysUpdate ( Request $request, $provider_id, $key_id )
    {
        $provider = Provider::find( $provider_id );
        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }
        $providerKey = $provider->providerKeys()->find( $key_id );
        if ( ! $providerKey )
        {
            return redirect()->route( 'providers.edit', $provider->id )
                ->withErrors( [ 'Ключ не найден' ] );
        }
        $rules = [
            'description'           => 'nullable',
            'ip'                    => 'nullable',
            'token_life'            => 'integer|min:1',
        ];
        $this->validate( $request, $rules );
        $providerKey->edit( $request->all() );
        return redirect()->route( 'providers.keys.edit', [ $provider->id, $providerKey->id ] )
            ->with( 'success', 'Ключ успешно отредактирован' );

    }

    public function keysDel ( Request $request, $id )
    {

        $rules = [
            'key_id'                 => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $provider->providerKeys()->find( $request->get( 'key_id' ) )->delete();

    }

    public function tokensDel ( Request $request, $id )
    {

        $rules = [
            'token_id'                 => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $providerKey = ProviderKey::find( $id );

        if ( ! $providerKey )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Ключ не найден' ] );
        }

        $providerKey->providerTokens()->find( $request->get( 'token_id' ) )->delete();

    }

    public function buildings ( Request $request, $id )
    {

        Title::add( 'Привязка Зданий' );

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $providerBuildings = $provider->buildings()
            ->orderBy( 'name' )
            ->paginate( config( 'pagination.per_page' ) );

        return view( 'admin.providers.buildings' )
            ->with( 'provider', $provider )
            ->with( 'providerBuildings', $providerBuildings );

    }

    public function buildingsSearch ( Request $request, $id )
    {

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';

        $buildings = Building
            ::select(
                Building::$_table . '.id',
                Building::$_table . '.name AS text'
            )
            ->where( Building::$_table . '.name', 'like', $s )
            ->whereNotIn( Building::$_table . '.id', $provider->buildings()->pluck( Building::$_table . '.id' ) )
            ->orderBy( Building::$_table . '.name' )
            ->get();

        return $buildings;

    }

    public function buildingsAdd ( Request $request, $id )
    {

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $provider->buildings()->attach( $request->get( 'buildings', [] ) );

        return redirect()->back()
            ->with( 'success', 'Адреса успешно назначены' );

    }

    public function buildingsDel ( Request $request, $id )
    {

        $rules = [
            'building_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $provider->buildings()->detach( $request->get( 'building_id' ) );

    }

    public function managements ( Request $request, $id )
    {

        Title::add( 'Привязка Зданий' );

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $providerManagements = $provider->managements()
            ->orderBy( 'name' )
            ->paginate( 30 );

        return view( 'admin.providers.managements' )
            ->with( 'provider', $provider )
            ->with( 'providerManagements', $providerManagements );

    }

    public function managementsSearch ( Request $request, $id )
    {

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';

        $res = Management
            ::where( Management::$_table . '.name', 'like', $s )
            ->whereNotIn( Management::$_table . '.id', $provider->managements()->pluck( Management::$_table . '.id' ) )
            ->orderBy( Management::$_table . '.name' )
            ->get();

        $managements = [];
        foreach ( $res as $r )
        {
            $name = $r->name;
            if ( $r->parent )
            {
                $name = $r->parent->name . ' ' . $name;
            }
            $managements[] = [
                'id'        => $r->id,
                'text'      => $name
            ];
        }

        return $managements;

    }

    public function managementsAdd ( Request $request, $id )
    {

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $provider->managements()->attach( $request->get( 'managements' ) );

        return redirect()->back()
            ->with( 'success', 'УО успешно привязаны' );

    }

    public function managementsDel ( Request $request, $id )
    {

        $rules = [
            'management_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $provider->managements()->detach( $request->get( 'management_id' ) );

    }

    public function managementsEmpty ( Request $request, $id )
    {

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $provider->managements()->detach();

    }

    public function types ( Request $request, $id )
    {

        Title::add( 'Привязка Классификатора' );

        $provider = Provider::find( $id );
        $search = trim( $request->get( 'search', '' ) );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $types = Type
            ::whereNotIn( 'id', $provider->types()->pluck( Type::$_table . '.id' ) )
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        $providerTypes = $provider->types()
            ->orderBy( 'name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', $search ) . '%';
            $providerTypes
                ->where( Type::$_table . '.name', 'like', $s );
        }

        $providerTypes = $providerTypes
            ->paginate( 30 )
            ->appends( $request->all() );

        return view( 'admin.providers.types' )
            ->with( 'provider', $provider )
            ->with( 'search', $search )
            ->with( 'types', $types )
            ->with( 'providerTypes', $providerTypes );

    }

    public function typesAdd ( Request $request, $id )
    {

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $provider->types()->attach( $request->get( 'types' ) );

        return redirect()->back()
            ->with( 'success', 'Классификатор успешно привязан' );

    }

    public function typesDel ( Request $request, $id )
    {

        $rules = [
            'type_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $provider->types()->detach( $request->get( 'type_id' ) );

    }

    public function typesEmpty ( Request $request, $id )
    {

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $provider->types()->detach();

        return redirect()->back()
            ->with( 'success', 'Привязки успешно удалены' );

    }

    public function create ()
    {

        Title::add( 'Создать регион' );

        return view('admin.providers.create' );

    }

    public function update ( Request $request, $id )
    {

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $rules = [
            'guid'                  => 'nullable|unique:providers,guid,' . $provider->id . ',id|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'username'              => 'nullable|string|max:50',
            'password'              => 'nullable|string|max:50',
            'need_act'              => 'boolean',
            'sms_auth'              => 'boolean',
        ];

        if ( $request->has( 'name' ) || $request->has( 'domain' ) )
        {
            $rules += [
                'name'                  => 'required|string|max:255',
                'domain'                => 'required|string|max:100',
            ];
        }

        $this->validate( $request, $rules );

        $provider->edit( $request->all() );

        return redirect()->route( 'providers.edit', $provider->id )
            ->with( 'success', 'Поставщик успешно отредактирован' );

    }

    public function store ( Request $request )
    {

        $rules = [
            'guid'                  => 'nullable|unique:providers,guid|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'username'              => 'nullable|string|max:50',
            'password'              => 'nullable|string|max:50',
            'name'                  => 'required|string|max:255',
            'domain'                => 'required|string|max:100',
            'need_act'              => 'boolean',
            'sms_auth'              => 'boolean',
        ];

        $this->validate( $request, $rules );

        $provider = Provider::create( $request->all() );

        if ( $provider instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $provider );
        }

        $provider->save();

        return redirect()->route( 'providers.edit', $provider->id )
            ->with( 'success', 'Поставщик успешно создан' );

    }

    public function uploadLogo ( Request $request, $id )
    {

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        $rules = [
            'file'                  => 'required|image|mimes:png',
        ];

        $this->validate( $request, $rules );

        $path = Storage::disk( 'public' )->putFile( 'logo', $request->file( 'file' ) );

        $provider->logo = $path;
        $provider->save();

        return redirect()->route( 'providers.edit', $provider->id )
            ->with( 'success', 'Логотип успешно загружен' );

    }

    public function deleteLogo ( Request $request, $id )
    {

        $provider = Provider::find( $id );

        if ( ! $provider )
        {
            return redirect()->route( 'providers.index' )
                ->withErrors( [ 'Поставщик не найден' ] );
        }

        unlink( public_path( '/storage/' . $provider->logo ) );

        $provider->logo = null;
        $provider->save();

        return redirect()->route( 'providers.edit', $provider->id )
            ->with( 'success', 'Логотип успешно загружен' );

    }

}
