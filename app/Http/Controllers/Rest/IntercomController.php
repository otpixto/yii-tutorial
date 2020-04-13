<?php

namespace App\Http\Controllers\Rest;

use App\Classes\Push;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IntercomController extends BaseController
{

    public function __construct ( Request $request )
    {
        $this->setLogs( storage_path( 'logs/rest_intercom.log' ) );
        parent::__construct( $request );
    }

    public function index ( Request $request, $route )
    {
        return $this->success( 'Hello, World!' );
    }

    public function login ( Request $request ) : Response
    {
        if ( ! $this->checkProviderKey( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }
        try
        {
            $validation = \Validator::make( $request->all(), [
                'username'          => 'required',
                'password'          => 'required|min:3|max:50',
            ]);
            if ( $validation->fails() )
            {
                return $this->error( $validation->errors()->first() );
            }
            $device = Device
                ::where( 'username', '=', $request->get( 'username' ) )
                ->where( 'password', '=', $request->get( 'password' ) )
                ->first();
            if ( ! $device )
            {
                return $this->error( trans('auth.failed' ), 403 );
            }
            $device->push_id = $request->get( 'push_id', null );
            $device->save();
            return $this->success([
                'message'            => 'OK',
            ]);
        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }
    }

    public function push ( Request $request ) : Response
    {
        $push = new Push( config( 'push.keys.intercom' ) );
        $push
            ->setTitle( $request->get( 'title' ) )
            ->setBody( $request->get( 'message' ) )
            ->setData( 'object', '1' )
            ->setData( 'id', '1' );
        $devices = Device
            ::whereNotNull( 'push_id' )
            ->get();
        $count = 0;
        $total = $devices->count();
        foreach ( $devices as $device )
        {
            $response = json_decode( $push->sendTo( $device->push_id ) );
            if ( $response->success )
            {
                $count ++;
            }
        }
        return $this->success([
            'message' => 'Sended to ' . $count . '/' . $total . ' devices'
        ]);
    }

}

