<?php

namespace App\Http\Controllers;

use App\Classes\Mosreg;
use App\Models\Building;
use App\Models\Management;
use App\Models\Ticket;
use App\Models\TicketManagement;
use App\Models\Type;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WebhookController extends Controller
{

    public function __construct ()
    {
        \Debugbar::disable();
    }

    public function ticket ( Request $request, $token )
    {

        try
        {

            $user = User::find( 149613 );
            if ( ! $user )
            {
                return [
                    'error' => 'User not found'
                ];
            }

            \Auth::login( $user );

            $management = Management
                ::where( 'webhook_token', '=', $token )
                ->first();
            if ( ! $management )
            {
                return [
                    'error' => 'Management not found'
                ];
            }

            if ( $request->json( 'webhook' ) == 'init' )
            {
                return [
                    'message' => 'OK'
                ];
            }

            $mosreg_status = $request->json( 'status_code' );
            $mosreg_id = $request->json( 'mosreg_id' );

            if ( ! isset( Ticket::$mosreg_statuses[ $mosreg_status ] ) )
            {
                return [
                    'error' => 'Status not found'
                ];
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
                    return [
                        'error' => 'Type not found'
                    ];
                }
                $building = Building
                    ::where( 'mosreg_id', '=', $request->json( 'address_id' ) )
                    ->first();
                if ( ! $building )
                {
                    return [
                        'error' => 'Address not found'
                    ];
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
            }

            if ( $ticketManagement->changeMosregStatus( $mosreg_status ) )
            {
                return [
                    'message' => 'OK'
                ];
            }
            else
            {
                return [
                    'error' => 'Failed to change status'
                ];
            }

            // Меняем статус на "В работе" сразу после поступления
            if ( $mosreg_status == 'NEW_CLAIM' && $management->hasMosreg( $mosreg ) )
            {
                $mosreg_status = 'IN_WORK';
                $res = $mosreg->changeStatus( $mosreg_id, $mosreg_status );
                if ( isset( $res->message ) && $res->message == 'OK' )
                {
                    if ( $ticketManagement->changeMosregStatus( $mosreg_status ) )
                    {
                        return [
                            'message' => 'OK'
                        ];
                    }
                    else
                    {
                        return [
                            'error' => 'Failed to change status'
                        ];
                    }
                }
            }

        }
        catch ( \Exception $e )
        {
            return [
                'error' => $e->getMessage()
            ];
        }

    }

}
