<?php

namespace App\Http\Controllers;

use App\Jobs\SendStream;
use App\Models\Building;
use App\Models\File;
use App\Models\Management;
use App\Models\Ticket;
use App\Models\TicketManagement;
use App\Models\Type;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;

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
                return $this->success( [
                    'message' => 'OK'
                ] );
            }
            elseif ( $request->json( 'webhook' ) == 'deinit' )
            {
                $management->webhook_active = 0;
                $management->webhook_token = null;
                $management->save();
                return $this->success( [
                    'message' => 'OK'
                ] );
            }

            $json = $request->get( 'ticket' );
            if ( empty( $json ) )
            {
                return $this->error( 'Empty JSON' );
            }
            $data = json_decode( $json );

            $mosreg_status = $data->status_code;
            $mosreg_id = $data->mosreg_id;

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
		            ::where( 'provider_id', '=', $management->provider_id )
                    ->where( 'mosreg_id', '=', $data->type_id )
                    ->first();
                if ( ! $type )
                {
                    if ( $management->provider )
                    {
                        $message = 'Не удалось опознать классификатор!' . PHP_EOL . PHP_EOL;
                        $message .= 'Ссылка на заявку: ' . $ticketManagement->getUrl() . PHP_EOL;
                        $message .= 'Номер в мосрег: ' . $data->mosreg_number . PHP_EOL . PHP_EOL;
                        $message .= 'Классификатор мосрега: ' . $data->type_name . ' (id:' . $data->type_id . ')' . PHP_EOL;
                        $management->provider->sendTelegramMessage( $message );
                    }
                    return $this->error( 'Type not found' );
                }
                $building = Building
                    ::where( 'provider_id', '=', $management->provider_id )
		            ->where( 'mosreg_id', '=', $data->address_id )
                    ->first();
                if ( ! $building )
                {
                    if ( $management->provider )
                    {
                        $message = 'Не удалось опознать адрес!' . PHP_EOL . PHP_EOL;
                        $message .= 'Ссылка на заявку: ' . $ticketManagement->getUrl() . PHP_EOL;
                        $message .= 'Номер в мосрег: ' . $data->mosreg_number . PHP_EOL . PHP_EOL;
                        $message .= 'Адрес мосрега: ' . $data->address_name . ' (id:' . $data->address_id . ')' . PHP_EOL;
                        $management->provider->sendTelegramMessage( $message );
                    }
                    return $this->error( 'Address not found' );
                }
                $ticket = new Ticket( [
                    'author_id' => \Auth::user()->id,
                    'provider_id' => $management->provider_id,
                    'type_id' => $type->id,
                    'building_id' => $building->id,
                    'flat' => $data->flat,
                    'actual_building_id' => $building->id,
                    'actual_flat' => $data->flat,
                    'phone' => mb_substr( $data->customer_phone, - 10 ),
                    'firstname' => $data->customer_name,
                    'place_id' => 1,
                    'text' => $data->text,
                    'vendor_id' => 1,
                    'vendor_number' => $data->mosreg_number,
                    'vendor_date' => Carbon::parse( $data->mosreg_created_at )
                        ->toDateTimeString(),
                ] );
                $ticket->save();
                $ticket->vendors()
                    ->attach( 1, [
                        'number' => $ticket->vendor_number,
                        'datetime' => $ticket->vendor_date,
                    ] );
                $ticketManagement = new TicketManagement( [
                    'ticket_id' => $ticket->id,
                    'management_id' => $management->id,
                    'mosreg_id' => $data->mosreg_id,
                ] );
                $ticketManagement->save();
                $this->dispatch( new SendStream( 'create', $ticketManagement ) );
            } else
            {
                $this->dispatch( new SendStream( 'update', $ticketManagement ) );
            }

            if ( $request->hasFile( 'files' ) )
            {
                foreach ( $request->file( 'files' ) as $_file )
                {
                    $path = Storage::putFile( 'files', $_file );
                    $file = File::create( [
                        'model_id' => $ticketManagement->id,
                        'model_name' => get_class( $ticketManagement ),
                        'path' => $path,
                        'name' => $_file->getClientOriginalName()
                    ] );
                    $file->save();
                }
            }

            $mosreg = null;
            if ( $management->hasMosreg( $mosreg ) )
            {
                switch ( $mosreg_status )
                {
                    case 'NEW_CLAIM':
                    case 'UNSATISFIED':
                        $mosreg_status = 'IN_WORK';
                        $responseData = $mosreg->toWork( $ticketManagement->mosreg_id );
                        if ( $responseData->message != 'OK' && $management->provider )
                        {
                            $message = 'Не удалось сменить статус на <b>' . $mosreg_status . '</b>!' . PHP_EOL;
                            $message .= 'Ошибка: ' . $responseData->error . PHP_EOL . PHP_EOL;
                            $message .= 'Ссылка на заявку: ' . $ticketManagement->getUrl() . PHP_EOL;
                            $message .= 'Номер в мосрег: ' . $data->mosreg_number . PHP_EOL . PHP_EOL;
                            $management->provider->sendTelegramMessage( $message );
                        }
                        break;
                }
            }

            if ( $ticketManagement->changeMosregStatus( $mosreg_status ) )
            {
                return $this->success( [
                    'message' => 'OK'
                ]);
            }
            else
            {
                return $this->error( 'Status not changed' );
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
