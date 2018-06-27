<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Models\Address;
use App\Models\Management;
use App\Models\Region;
use App\Models\RegionPhone;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class RegionsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Регионы' );
    }

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );
        $address = $request->get( 'address', null );

        $regions = Region
            ::orderBy( Region::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $regions
                ->where( Region::$_table . '.name', 'like', $s );
        }

        if ( ! empty( $address ) )
        {
            $regions
                ->whereHas( 'addresses', function ( $q ) use ( $address )
                {
                    return $q
                        ->where( Address::$_table . '.id', '=', $address );
                });
        }

        $regions = $regions
            ->paginate( 30 )
            ->appends( $request->all() );

        return view('admin.regions.index' )
            ->with( 'regions', $regions );

    }

    public function show ( $id )
    {
        return redirect()->route( 'regions.index' );
    }

    public function edit ( $id )
    {

        Title::add( 'Редактировать регион' );

        $region = Region::find( $id );

        if ( ! $region )
        {
            return redirect()->route( 'regions.index' )
                ->withErrors( [ 'Регион не найден' ] );
        }

        return view('admin.regions.edit' )
            ->with( 'region', $region );

    }

    public function addresses ( Request $request, $id )
    {

        Title::add( 'Привязка Зданий' );

        $region = Region::find( $id );

        if ( ! $region )
        {
            return redirect()->route( 'regions.index' )
                ->withErrors( [ 'Регион не найден' ] );
        }

        $regionAddresses = $region->addresses()
            ->orderBy( 'name' )
            ->paginate( 30 );

        return view( 'admin.regions.addresses' )
            ->with( 'region', $region )
            ->with( 'regionAddresses', $regionAddresses );

    }

    public function addressesSearch ( Request $request, $id )
    {

        $region = Region::find( $id );

        if ( ! $region )
        {
            return redirect()->back()
                ->withErrors( [ 'Регион не найден' ] );
        }

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';

        $addresses = Address
            ::select(
                Address::$_table . '.id',
                Address::$_table . '.name AS text'
            )
            ->where( Address::$_table . '.name', 'like', $s )
            ->whereNotIn( Address::$_table . '.id', $region->addresses()->pluck( Address::$_table . '.id' ) )
            ->orderBy( Address::$_table . '.name' )
            ->get();

        return $addresses;

    }

    public function addressesAdd ( Request $request, $id )
    {

        $region = Region::find( $id );

        if ( ! $region )
        {
            return redirect()->back()
                ->withErrors( [ 'Регион не найден' ] );
        }

        $region->addresses()->attach( $request->get( 'addresses', [] ) );

        return redirect()->back()
            ->with( 'success', 'Адреса успешно назначены' );

    }

    public function addressesDel ( Request $request, $id )
    {

        $rules = [
            'address_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $region = Region::find( $id );

        if ( ! $region )
        {
            return redirect()->back()
                ->withErrors( [ 'Регион не найден' ] );
        }

        $region->addresses()->detach( $request->get( 'address_id' ) );

    }

    public function managements ( Request $request, $id )
    {

        Title::add( 'Привязка Зданий' );

        $region = Region::find( $id );

        if ( ! $region )
        {
            return redirect()->route( 'regions.index' )
                ->withErrors( [ 'Регион не найден' ] );
        }

        $regionManagements = $region->managements()
            ->orderBy( 'name' )
            ->paginate( 30 );

        return view( 'admin.regions.managements' )
            ->with( 'region', $region )
            ->with( 'regionManagements', $regionManagements );

    }

    public function managementsSearch ( Request $request, $id )
    {

        $region = Region::find( $id );

        if ( ! $region )
        {
            return redirect()->route( 'regions.index' )
                ->withErrors( [ 'Регион не найден' ] );
        }

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';

        $managements = Management
            ::select(
                Management::$_table . '.id',
                Management::$_table . '.name AS text'
            )
            ->where( Management::$_table . '.name', 'like', $s )
            ->whereNotIn( Management::$_table . '.id', $region->managements()->pluck( Management::$_table . '.id' ) )
            ->orderBy( Management::$_table . '.name' )
            ->get();

        return $managements;

    }

    public function managementsAdd ( Request $request, $id )
    {

        $region = Region::find( $id );

        if ( ! $region )
        {
            return redirect()->route( 'regions.index' )
                ->withErrors( [ 'Регион не найден' ] );
        }

        $region->managements()->attach( $request->get( 'managements' ) );

        return redirect()->back()
            ->with( 'success', 'УО успешно привязаны' );

    }

    public function managementsDel ( Request $request, $id )
    {

        $rules = [
            'management_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $region = Region::find( $id );

        if ( ! $region )
        {
            return redirect()->route( 'regions.index' )
                ->withErrors( [ 'Регион не найден' ] );
        }

        $region->managements()->detach( $request->get( 'management_id' ) );

    }

    public function types ( Request $request, $id )
    {

        Title::add( 'Привязка Классификатора' );

        $region = Region::find( $id );

        if ( ! $region )
        {
            return redirect()->route( 'regions.index' )
                ->withErrors( [ 'Регион не найден' ] );
        }

        $types = Type
            ::whereNotIn( 'id', $region->types()->pluck( Type::$_table . '.id' ) )
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        $regionTypes = $region->types()
            ->orderBy( 'name' )
            ->paginate( 30 );

        return view( 'admin.regions.types' )
            ->with( 'region', $region )
            ->with( 'types', $types )
            ->with( 'regionTypes', $regionTypes );

    }

    public function typesAdd ( Request $request, $id )
    {

        $region = Region::find( $id );

        if ( ! $region )
        {
            return redirect()->route( 'regions.index' )
                ->withErrors( [ 'Регион не найден' ] );
        }

        $region->types()->attach( $request->get( 'types' ) );

        return redirect()->back()
            ->with( 'success', 'Классификатор успешно привязан' );

    }

    public function typesDel ( Request $request, $id )
    {

        $rules = [
            'type_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $region = Region::find( $id );

        if ( ! $region )
        {
            return redirect()->route( 'regions.index' )
                ->withErrors( [ 'Регион не найден' ] );
        }

        $region->types()->detach( $request->get( 'type_id' ) );

    }

    public function create ()
    {

        Title::add( 'Создать регион' );

        return view('admin.regions.create' );

    }

    public function update ( Request $request, $id )
    {

        $region = Region::find( $id );

        if ( ! $region )
        {
            return redirect()->route( 'regions.index' )
                ->withErrors( [ 'Регион не найден' ] );
        }

        $rules = [
            'guid'                  => 'nullable|unique:regions,guid,' . $region->id . ',id|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'username'              => 'nullable|string|max:50',
            'password'              => 'nullable|string|max:50',
        ];

        if ( $request->has( 'name' ) || $request->has( 'domain' ) )
        {
            $rules += [
                'name'                  => 'required|string|max:255',
                'domain'                => 'required|string|max:100',
            ];
        }

        $this->validate( $request, $rules );

        $region->edit( $request->all() );

        return redirect()->route( 'regions.edit', $region->id )
            ->with( 'success', 'Регион успешно отредактирован' );

    }

    public function store ( Request $request )
    {

        $rules = [
            'guid'                  => 'nullable|unique:regions,guid|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'username'              => 'nullable|string|max:50',
            'password'              => 'nullable|string|max:50',
            'name'                  => 'required|string|max:255',
            'domain'                => 'required|string|max:100',
        ];

        $this->validate( $request, $rules );

        $region = Region::create( $request->all() );

        if ( $region instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $region );
        }

        $region->save();

        return redirect()->route( 'regions.edit', $region->id )
            ->with( 'success', 'Регион успешно добавлен' );

    }

    public function phonesAdd ( Request $request, $id )
    {
        $rules = [
            'phone'                 => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        ];
        $this->validate( $request, $rules );
        $region = Region::find( $id );
        if ( ! $region )
        {
            return redirect()->back()
                ->withErrors( [ 'Регион не найден' ] );
        }
        $phone = mb_substr( preg_replace( '/[^0-9]/', '', str_replace( '+7', '', $request->get( 'phone' ) ) ), -10 );
        $regionPhone = $region->addPhone( $phone );
        if ( $regionPhone instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $regionPhone );
        }
        $regionPhone->save();
        return redirect()->route( 'regions.edit', $region->id )
            ->with( 'success', 'Телефон успешно добавлен' );
    }

    public function phonesDel ( Request $request, $id )
    {
        $rules = [
            'phone_id'                 => 'required|integer',
        ];
        $this->validate( $request, $rules );
        $region = Region::find( $id );
        if ( ! $region )
        {
            return redirect()->back()
                ->withErrors( [ 'Регион не найден' ] );
        }
        $region->phones()->find( $request->get( 'phone_id' ) )->delete();
    }

}
