<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Address;
use App\Models\Management;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class AddressesController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Здания' );
    }

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );
        $region = $request->get( 'region' );
        $management = $request->get( 'management' );

        $regions = Region
            ::mine()
            ->current()
            ->orderBy( Region::$_table . '.name' )
            ->get();

        $addresses = Address
            ::mine( Address::IGNORE_MANAGEMENT )
            ->orderBy( Address::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $addresses
                ->where( Address::$_table . '.name', 'like', $s )
                ->orWhere( Address::$_table . '.guid', 'like', $s )
                ->orWhere( Address::$_table . '.hash', '=', Address::genHash( $search ) );
        }

        if ( ! empty( $region ) )
        {
            $addresses
                ->whereHas( 'regions', function ( $regions ) use ( $region )
                {
                    return $regions
                        ->where( Region::$_table . '.id', '=', $region );
                });
        }

        if ( ! empty( $management ) )
        {
            $addresses
                ->whereHas( 'managements', function ( $q ) use ( $management )
                {
                    return $q
                        ->where( Management::$_table . '.id', '=', $management );
                });
        }

        $addresses = $addresses
            ->paginate( 30 )
            ->appends( $request->all() );

        return view( 'catalog.addresses.index' )
            ->with( 'addresses', $addresses )
            ->with( 'regions', $regions );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {
        Title::add( 'Добавить здание' );
        return view( 'catalog.addresses.create' );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store ( Request $request )
    {

        $rules = [
            'guid'                  => 'nullable|unique:addresses,guid|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'name'                  => 'required|unique:addresses,name|max:255',
        ];

        $this->validate( $request, $rules );

        $address = Address::create( $request->all() );
        if ( $address instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $address );
        }
        $address->save();

        self::clearCache();

        return redirect()->route( 'addresses.index' )
            ->with( 'success', 'Адрес успешно добавлен' );

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show ( $id )
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit ( $id )
    {

        Title::add( 'Редактировать здание' );

        $address = Address::find( $id );

        if ( ! $address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }

        $addressRegionsCount = $address->regions()
            ->count();

        $addressManagementsCount = $address->managements()
            ->count();

        return view( 'catalog.addresses.edit' )
            ->with( 'address', $address )
            ->with( 'addressRegionsCount', $addressRegionsCount )
            ->with( 'addressManagementsCount', $addressManagementsCount );

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update ( Request $request, $id )
    {

        $address = Address::find( $id );
        if ( ! $address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }
		
		$rules = [
            'guid'                  => 'nullable|unique:addresses,guid,' . $address->id . '|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'name'                  => 'required|unique:addresses,name,' . $address->id . '|max:255',
        ];

        $this->validate( $request, $rules );

        $res = $address->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }

        self::clearCache();

        return redirect()->route( 'addresses.edit', $address->id )
            ->with( 'success', 'Адрес успешно отредактирован' );

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy ( $id )
    {
        //
    }

    public function search ( Request $request )
    {

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';
        $region_id = $request->get( 'region_id', Region::getCurrent() ? Region::$current_region->id : null );

        $addresses = Address
            ::mine( Address::IGNORE_REGION, Address::IGNORE_MANAGEMENT )
            ->select(
                'id',
                'name AS text'
            )
            ->where( 'name', 'like', $s )
            ->orderBy( 'name' );

        if ( ! empty( $region_id ) )
        {
            $addresses
                ->whereHas( 'regions', function ( $regions ) use ( $region_id )
                {
                    return $regions
                        ->where( Region::$_table . '.id', '=', $region_id );
                });
        }

        $addresses = $addresses
            ->get();

        return $addresses;

    }

    public function managements ( Request $request, $id )
    {

        Title::add( 'Привязка УО' );

        $address = Address::find( $id );

        if ( ! $address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        $addressManagements = $address->managements()
            ->orderBy( Management::$_table . '.name' )
            ->paginate( 30 );

        return view( 'catalog.addresses.managements' )
            ->with( 'address', $address )
            ->with( 'addressManagements', $addressManagements );

    }

    public function managementsSearch ( Request $request, $id )
    {

        $address = Address::find( $id );

        if ( ! $address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';

        $managements = Management
            ::mine()
            ->select(
                Management::$_table . '.id',
                Management::$_table . '.name AS text'
            )
            ->where( Management::$_table . '.name', 'like', $s )
            ->whereNotIn( Management::$_table . '.id', $address->managements()->pluck( Management::$_table . '.id' ) )
            ->orderBy( Management::$_table . '.name' )
            ->get();

        return $managements;

    }

    public function managementsAdd ( Request $request, $id )
    {

        $address = Address::find( $id );

        if ( ! $address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }

        $address->managements()->attach( $request->get( 'managements' ) );

        return redirect()->back()
            ->with( 'success', 'УО успешно привязаны' );

    }

    public function managementsDel ( Request $request, $id )
    {

        $rules = [
            'management_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $address = Address::find( $id );

        if ( ! $address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Адрес не найден' ] );
        }

        $address->managements()->detach( $request->get( 'management_id' ) );

    }

    public function regions ( Request $request, $address_id )
    {

        Title::add( 'Привязка регионов' );

        $address = Address::find( $address_id );

        if ( ! $address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        $addressRegions = $address->regions()
            ->orderBy( Region::$_table . '.name' )
            ->paginate( 30 );

        $regions = Region
            ::mine()
            ->whereNotIn( 'id', $address->regions()->pluck( Region::$_table . '.id' ) )
            ->pluck( Region::$_table . '.name', Region::$_table . '.id' );

        return view( 'catalog.addresses.regions' )
            ->with( 'address', $address )
            ->with( 'addressRegions', $addressRegions )
            ->with( 'regions', $regions );

    }

    public function regionsAdd ( Request $request, $id )
    {

        $address = Address::find( $id );

        if ( ! $address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        $address->regions()->attach( $request->get( 'regions' ) );

        return redirect()->route( 'addresses.regions', $address->id )
            ->with( 'success', 'Привязка прошла успешно' );

    }

    public function regionsDel ( Request $request, $id )
    {

        $rules = [
            'region_id'             => 'required|integer',
        ];

        $this->validate( $request, $rules );

        $address = Address::find( $id );

        if ( ! $address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        $address->regions()->detach( $request->get( 'region_id' ) );

    }

    public function regionsEmpty ( Request $request, $address_id )
    {

        $address = Address::find( $address_id );

        if ( ! $address )
        {
            return redirect()->route( 'addresses.index' )
                ->withErrors( [ 'Здание не найдено' ] );
        }

        $address->regions()->detach();

    }

}
