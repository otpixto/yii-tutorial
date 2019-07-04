<?php

namespace App\Http\Controllers;

use App\Jobs\SendStream;
use App\Models\Building;
use App\Models\Management;
use App\Models\Ticket;
use App\Models\TicketManagement;
use App\Models\Type;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookController extends Controller
{

    public function __construct ()
    {
        \Debugbar::disable();
    }

    public function ticket ( Request $request, $token )
    {

        \DB::beginTransaction();

        try
        {

            $user = User::find( config( 'gzhi.user_id' ) );
            if ( ! $user )
            {
                return $this->error( 'User not found' );
            }

            \Auth::login( $user );

            $management = Management
                ::where( 'webhook_token', '=', $token )
                ->first();
            if ( ! $management )
            {
                return $this->error( 'Management not found' );
            }

            if ( $request->json( 'webhook' ) == 'init' )
            {
                return $this->success([
                    'message' => 'OK'
                ]);
            }

            $mosreg_status = $request->json( 'status_code' );
            $mosreg_id = $request->json( 'mosreg_id' );

            if ( ! isset( Ticket::$mosreg_statuses[ $mosreg_status ] ) )
            {
                return $this->error( 'Status not found' );
            }

            $ticketManagement = $management->tickets()
                ->where( 'mosreg_id', '=', $mosreg_id )
                ->first();
            if ( ! $ticketManagement )
            {
                $type = Type
                    ::where( 'mosreg_id', '=', $request->json( 'type_id' ) )
                    ->first();
                if ( ! $type )
                {
                    return $this->error( 'Type not found' );
                }
                $building = Building
                    ::where( 'mosreg_id', '=', $request->json( 'address_id' ) )
                    ->first();
                if ( ! $building )
                {
                    return $this->error( 'Address not found' );
                }
                $ticket = new Ticket([
                    'author_id'                 => \Auth::user()->id,
                    'provider_id'               => $management->provider_id,
                    'type_id'                   => $type->id,
                    'building_id'               => $building->id,
                    'flat'                      => $request->json( 'flat' ),
                    'actual_building_id'        => $building->id,
                    'actual_flat'               => $request->json( 'flat' ),
                    'phone'                     => mb_substr( $request->json( 'customer_phone' ), -10 ),
                    'firstname'                 => $request->json( 'customer_name' ),
                    'place_id'                  => 1,
                    'text'                      => $request->json( 'text' ),
                    'vendor_id'                 => 1,
                    'vendor_number'             => $request->json( 'mosreg_number' ),
                    'vendor_date'               => Carbon::parse( $request->json( 'mosreg_created_at' ) )->toDateTimeString(),
                ]);
                $ticket->save();
                $ticketManagement = new TicketManagement([
                    'ticket_id'         => $ticket->id,
                    'management_id'     => $management->id,
                    'mosreg_id'         => $request->json( 'mosreg_id' ),
                ]);
                $ticketManagement->save();
                $this->dispatch( new SendStream( 'create', $ticketManagement ) );
            }
            else
            {
                $this->dispatch( new SendStream( 'update', $ticketManagement ) );
            }

            $mosreg = null;
            if ( $management->hasMosreg( $mosreg ) )
            {
                switch ( $mosreg_status )
                {
                    case 'NEW_CLAIM':
                    case 'UNSATISFIED':
                        $mosreg_status = 'IN_WORK';
                        break;
                }
            }

            if ( $ticketManagement->changeMosregStatus( $mosreg_status ) )
            {
                return $this->success([
                    'message' => 'OK'
                ]);
            }
            else
            {
                return $this->error( 'Failed to change status' );
            }

        }
        catch ( \Exception $e )
        {
            return $this->error( $e->getMessage() );
        }

    }

    protected function error ( $error, $httpCode = null ) : Response
    {
        if ( \DB::transactionLevel() )
        {
            \DB::rollback();
        }
        return response( compact( 'error' ), $httpCode ?: 200 );
    }

    protected function success ( $response ) : Response
    {
        if ( \DB::transactionLevel() )
        {
            \DB::commit();
        }
        return response( $response, 200 );
    }

}
