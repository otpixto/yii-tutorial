<?php

namespace App\Http\Controllers;


use App\Classes\Devices;
use App\Models\Ticket;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DeviceController extends Controller
{

    const CACHE_LIFE_MINUTES = 60;
    const UPDATES_LIFE_SECONDS = 10;

    private function authToken ( Request $request, & $output = null ) : bool
    {

        $timestamp = Carbon::now()->timestamp - self::UPDATES_LIFE_SECONDS;

        $validation = \Validator::make( $request->all(), [
            'token'         => 'required',
        ]);

        if ( $validation->fails() )
        {
            foreach ( $validation->errors() as $error )
            {
                $output = $error->getMessage();
                return false;
            }
        }

        $token = $request->get( 'token' );

        if ( ! \Cache::has( 'device.token.' . $token ) )
        {
            $output = trans('device.token' );
            return false;
        }

        $data = \Cache::get( 'device.token.' . $token );

        $user = User::find( $data[ 0 ] );

        if ( ! $user )
        {
            $output = trans('device.user_not_found' );
            return false;
        }

        if ( ! $user->isActive() )
        {
            $output = trans('device.user_not_active' );
            return false;
        }

        \Auth::login( $user );

        $output = $data;
        $data[ 1 ] = $timestamp;

        \Cache::put( 'device.token.' . $token, $data, self::CACHE_LIFE_MINUTES );
        \Cache::put( 'device.user.' . $user->id, $token, self::CACHE_LIFE_MINUTES );

        return true;

    }

    public function auth ( Request $request ) : array
    {

        $timestamp = Carbon::now()->timestamp - self::UPDATES_LIFE_SECONDS;

        $validation = \Validator::make( $request->all(), [
            'email'         => 'required|email',
            'password'      => 'required|min:3|max:50',
        ]);

        if ( $validation->fails() )
        {
            return [ 'errors' => $validation->errors() ];
        }

        if ( ! \Auth::guard()->attempt( $request->toArray() ) )
        {
            return [ 'errors' => [ 'username' => trans('auth.failed' ) ] ];
        }

        $user = \Auth::user();

        $token = md5( $user->id . $user->getName() . time() );

        if ( \Cache::has( 'device.user.' . $user->id ) )
        {
            $old_token = \Cache::get( 'device.user.' . $user->id );
            if ( \Cache::has( 'device.token.' . $old_token ) )
            {
                \Cache::forget( 'device.token.' . $old_token );
            }
        }

        \Cache::put( 'device.token.' . $token, [ $user->id, $timestamp ], self::CACHE_LIFE_MINUTES );
        \Cache::put( 'device.user.' . $user->id, $token, self::CACHE_LIFE_MINUTES );

        return [
            'id'            => $user->id,
            'fullname'      => $user->getName(),
            'token'         => $token
        ];

    }

    public function tickets ( Request $request ) : array
    {

        if ( ! $this->authToken( $request, $data ) )
        {
            return $this->error( $data );
        }

        $tickets = Ticket
            ::mine()
            ->where( 'status_code', '!=', 'draft' )
            ->orderBy( 'id', 'desc' )
            ->with(
                'comments',
                'building',
                'author'
            )
            ->get();

        return Devices::ticketsInfo( $tickets );

    }

    public function updates ( Request $request ) : array
    {

        if ( ! $this->authToken( $request, $data ) )
        {
            return $this->error( $data );
        }

        $response = [];

        $ticketsAdded = Ticket
            ::mine()
            ->where( 'status_code', '!=', 'draft' )
            ->where( 'created_at', '>=', date( 'Y-m-d H:i:s', $data[ 1 ] ) )
            ->orderBy( 'id', 'desc' )
            ->with(
                'comments',
                'building',
                'author'
            )
            ->get();

        $response[ 'added' ] = Devices::ticketsInfo( $ticketsAdded );

        $ticketsUpdated = Ticket
            ::mine()
            ->where( 'status_code', '!=', 'draft' )
            ->where( 'updated_at', '>=', date( 'Y-m-d H:i:s', $data[ 1 ] ) )
            ->whereRaw( 'updated_at != created_at' )
            ->orderBy( 'id', 'desc' )
            ->with(
                'comments',
                'building',
                'author'
            )
            ->get();

        $response[ 'updated' ] = Devices::ticketsInfo( $ticketsUpdated );

        $ticketsDeleted = Ticket
            ::mine()
            ->withTrashed()
            ->where( 'status_code', '!=', 'draft' )
            ->where( 'deleted_at', '>=', date( 'Y-m-d H:i:s', $data[ 1 ] ) )
            ->pluck( 'id' )
            ->toArray();

        $response[ 'deleted' ] = $ticketsDeleted;

        return $response;

    }

    private function error ( $error ) : array
    {
        return compact( 'error' );
    }

}