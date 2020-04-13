<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Models\File;
use App\Models\Region;
use App\Models\TicketManagement;
use App\Models\TicketStatus;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use App\Models\Building;
use Illuminate\Console\Command;
use Illuminate\Support\MessageBag;
use App\Models\Management;
use App\Models\Category;
use App\Models\Type;
use App\Models\Customer;
use App\Models\Ticket;
use Illuminate\Support\Str;

class ImportVerin extends Command
{

	protected $signature = 'import:verin';

	public function __construct ()
	{
        $this->client = new Client();
		parent::__construct ();
	}

	public function handle ()
	{

	    $verin_id = 149119;
	    $admin_id = 1;

	    try
        {

            $api_url = 'https://mo.i-eds.ru/api/';

            $headers = [
                'Authorization'         => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiIwYzY5MDNmMi0yOWU3LTQyNmMtOTI4Zi0yYWQzZjk0MWQ2MjciLCJzdWIiOiJtdmVyaW5AbWFpbC5ydSIsInR5cGUiOiJlbWFpbCIsImlhdCI6MTUzMDAyMDMxOCwianRpIjoiMDk4Y2VmYTc4MGRlMDUzYmE1YWJhMjQ1YTE1MDgyZTY2N2U3ZjBiNyJ9.ENhadqF_iqEfaPPCZJ7h23OJTO7jJa9tZXJH-cy5lRE',
                'Content-Type'          => 'application/json',
            ];

            $db = \DB::connection( 'eds_verin' );

            //$db->beginTransaction();

            /*
            //$db->table( 'managements' )->delete();

            $managements = json_decode( file_get_contents( storage_path( 'json/managements.json' ) ) );

            $categories = [
                "УК" => 1,
                "РСО" => 3,
                "СЭ" => 4,
                "ТСЖ" => 2,
            ];

            foreach ( $managements->data as $r )
            {
                $management = Management::find( $r->id );
                if ( $management )
                {
                    $management->parent_id = $r->parent_id;
                }
                else
                {
                    $management = new Management;
                    $management->id = $r->id;
                    $management->parent_id = $r->parent_id;
                    $management->name = $r->title;
                    $management->guid = $r->gzhi_guid;
                    $management->phone = $r->contact_phone;
                    $management->address_text_old = $r->real_address;
                    $management->director = $r->director_name;
                    $management->category_id = $categories[ $r->type ];
                }
                $management->save();
            }
            */

            //$db->table( 'categories' )->delete();
            //$db->table( 'types' )->delete();

            $types = json_decode( file_get_contents( storage_path( 'json/types.json' ) ) );

            foreach ( $types->data as $r )
            {

                $category = Category::find( $r->service_qualifier_id );
                if ( ! $category )
                {
                    $category = new Category;
                    $category->id = $r->service_qualifier_id;
                }
                $code = $r->code;
                if ( mb_strlen( $code ) < 2 )
                {
                    $code = '0' . $code;
                }
                $category->name = $code . '. ' . $r->title;
                $category->save();

                $type = Type::find( $r->service_qualifier_id );
                if ( ! $type )
                {
                    $type = new Type;
                    $type->id = $r->service_qualifier_id;
                }
                $type->name = $code . '. ' . $r->title;
                $type->category_id = $category->id;
                $type->emergency = $r->is_emergency ? 1 : 0;
                $type->period_acceptance = $r->accept_time;
                $type->period_execution = $r->execution_time;
                $type->guid = $r->gzhi_guid;
                $type->need_act = $r->is_act_required ? 1 : 0;
                $type->save();

                foreach ( $r->children as $c )
                {
                    $type = Type::find( $c->service_qualifier_id );
                    if ( ! $type )
                    {
                        $type = new Type;
                        $type->id = $c->service_qualifier_id;
                    }
                    $code = str_replace( '-', '.', $c->code );
                    $exp = explode( '.', $code );
                    foreach ( $exp as & $e )
                    {
                        if ( mb_strlen( $e ) < 2 )
                        {
                            $e = '0' . $e;
                        }
                    }
                    $code = implode( '.', $exp );
                    $type->name = $code . '. ' . $c->title;
                    $type->category_id = $category->id;
                    $type->emergency = $c->is_emergency ? 1 : 0;
                    $type->period_acceptance = $c->accept_time;
                    $type->period_execution = $c->execution_time;
                    $type->guid = $c->gzhi_guid;
                    $type->need_act = $c->is_act_required ? 1 : 0;
                    $type->save();
                    $parent_id = $type->id;
                    //$type->managements()->sync( Management::pluck( 'id' ) );
                    //$type->regions()->attach( Region::pluck( 'id' ) );
                    foreach ( $c->children as $c2 )
                    {
                        $type = Type::find( $c2->service_qualifier_id );
                        if ( ! $type )
                        {
                            $type = new Type;
                            $type->id = $c2->service_qualifier_id;
                        }
                        $code = str_replace( '-', '.', $c2->code );
                        $exp = explode( '.', $code );
                        foreach ( $exp as & $e )
                        {
                            if ( mb_strlen( $e ) < 2 )
                            {
                                $e = '0' . $e;
                            }
                        }
                        $code = implode( '.', $exp );
                        $type->parent_id = $parent_id;
                        $type->name = $code . '. ' . $c2->title;
                        $type->category_id = $category->id;
                        $type->emergency = $c2->is_emergency ? 1 : 0;
                        $type->period_acceptance = $c2->accept_time;
                        $type->period_execution = $c2->execution_time;
                        $type->guid = $c2->gzhi_guid;
                        $type->need_act = $c2->is_act_required ? 1 : 0;
                        $type->save();
                        //$type->managements()->attach( Management::pluck( 'id' ) );
                        //$type->regions()->attach( Region::pluck( 'id' ) );
                    }
                }
            }

            /*
            $db->table( 'addresses' )->delete();

            if ( $handle = opendir( storage_path( 'json/addresses' ) ) )
            {
                while ( false !== ( $file = readdir( $handle ) ) )
                {
                    if ( $file != '.' && $file != '..' )
                    {

                        $addresses = json_decode( file_get_contents( storage_path( 'json/addresses/' . $file ) ) );

                        foreach ( $addresses->data as $r )
                        {
                            $address = Address::create([
                                'name' => $r->full_address,
                                'guid' => $r->gzhi_guid,
                                'lon' => $r->longitude,
                                'lat' => $r->latitude,
                            ]);
                            if ( $address instanceof MessageBag )
                            {
                                echo $address->name . PHP_EOL;
                                print_r( $address );
                            }
                            else
                            {
                                $address->id = $r->building_id;
                                $address->save();
                            }
                        }

                    }
                }
                closedir( $handle );
            }
            */

            /*
            //$db->table( 'customers' )->delete();

            if ( $handle = opendir( storage_path( 'json/customers' ) ) )
            {
                while ( false !== ( $file = readdir( $handle ) ) )
                {
                    if ( $file != '.' && $file != '..' )
                    {

                        $customers = json_decode( file_get_contents( storage_path( 'json/customers/' . $file ) ) );

                        foreach ( $customers->data as $r )
                        {
                            $customer = Customer::create([
                                'firstname' => $r->first_name,
                                'middlename' => $r->middle_name,
                                'lastname' => $r->last_name,
                                'phone' => $r->phone,
                                'actual_address_id' => $r->rooms[ 0 ]->building_id,
                                'email' => $r->email,
                            ]);
                            if ( $customer instanceof MessageBag )
                            {
                                echo $customer->getName() . PHP_EOL;
                                print_r( $customer );
                            }
                            else
                            {
                                $customer->id = $r->user_id;
                                $customer->save();
                            }
                        }

                    }
                }
                closedir( $handle );
            }
            */

            /*
            //$db->table( 'users' )->delete();

            if ( $handle = opendir( storage_path( 'json/users' ) ) )
            {
                while ( false !== ( $file = readdir( $handle ) ) )
                {
                    if ( $file != '.' && $file != '..' )
                    {

                        $users = json_decode( file_get_contents( storage_path( 'json/users/' . $file ) ) );

                        foreach ( $users->data as $r )
                        {
                            $user = User::find( $r->id );
                            if ( ! $user )
                            {
                                $user = new User;
                                $user->fill([
                                    'firstname' => $r->first_name,
                                    'middlename' => $r->middle_name,
                                    'lastname' => $r->last_name,
                                    'email' => $r->email,
                                ]);
                                $user->id = $r->id;
                            }
                            if ( $r->telegram_chat_id )
                            {
                                $user->telegram_id = $r->telegram_chat_id;
                            }
                            $user->phone = mb_substr( $r->phone, -10 );
                            try
                            {
                                $user->save();
                            }
                            catch ( QueryException $e )
                            {

                            }
                        }

                    }
                }
                closedir( $handle );
            }
            */

            /*
            $this->info( 'Tickets Start' );
            $db->table( 'tickets' )->delete();
            $db->table( 'comments' )->delete();
            $db->table( 'files' )->delete();

            $statuses = [
                2 => 'transferred',
                3 => 'assigned',
                5 => 'accepted',
                6 => 'assigned',
                8 => 'rejected',
                10 => 'completed_with_act',
                11 => 'closed_with_confirm',
                14 => 'transferred_again',
                15 => 'from_lk',
                16 => 'cancel',
            ];

            $files = scandir( storage_path( 'json/tickets' ) );
            sort( $files, SORT_NUMERIC );

            foreach ( $files as $file )
            {
                if ( $file != '.' && $file != '..' )
                {

                    $tickets = json_decode( file_get_contents( storage_path( 'json/tickets/' . $file ) ) );
                    $this->info( 'File ' . $file . ' Start' );
                    $bar = $this->output->createProgressBar( count( $tickets->data ) );

                    foreach ( $tickets->data as $r )
                    {

                        $bar->advance();

                        if ( Ticket::find( $r->id ) ) continue;
                        if ( ! $r->division_id && ! $r->company_id ) continue;

                        switch ( $r->status )
                        {
                            case 7:
                                if ( $r->act_id )
                                {
                                    $status_code = 'completed_with_act';
                                }
                                else
                                {
                                    $status_code = 'completed_without_act';
                                }
                                break;
                            case 11:
                                if ( $r->rating && $r->rating != -1 )
                                {
                                    $status_code = 'closed_with_confirm';
                                }
                                else
                                {
                                    $status_code = 'closed_without_confirm';
                                }
                                break;
                            default:
                                if ( isset( $statuses[ $r->status ] ) )
                                {
                                    $status_code = $statuses[ $r->status ];
                                }
                                else
                                {
                                    continue;
                                }
                                break;
                        }

                        $address = Address::find( $r->building_id );
                        if ( ! $address ) continue;

                        $response = $this->client->get( $api_url . 'issues/' . $r->id, [
                            'headers' => $headers
                        ]);

                        $json_string = $response->getBody();
                        if ( empty( $json_string ) ) continue;
                        $data = json_decode( $json_string );

                        $response = $this->client->get( $api_url . 'issues/history/' . $r->id, [
                            'headers' => $headers
                        ]);

                        $json_string = $response->getBody();
                        if ( empty( $json_string ) ) continue;
                        $history = json_decode( $json_string );

                        $first_history = end( $history->data );

                        $ticket = new Ticket;

                        $customer = Customer::find( $r->client_id );

                        $ticket->fill([
                            'urgently' => $r->is_urgent ? 1 : 0,
                            'emergency' => $r->is_emergency ? 1 : 0,
                            'dobrodel' => $r->is_goodmaker ? 1 : 0,
                            'type_id' => $r->service_qualifier_id,
                            'address_id' => $r->building_id,
                            'flat' => $data->data->room_number,
                            'place_id' => 2,
                            'text' => $r->description,
                            'phone' => mb_substr( $r->client_phone, -10 )
                        ]);

                        if ( $customer )
                        {
                            $ticket->customer_id = $customer->id;
                            $ticket->lastname = $customer->lastname;
                            $ticket->firstname = $customer->firstname;
                            $ticket->middlename = $customer->middlename;
                        }
                        else
                        {
                            $exp = explode( ' ', $r->client_name );
                            $ticket->lastname = $exp[ 0 ] ?? '-';
                            $ticket->firstname = $exp[ 1 ] ?? '-';
                            $ticket->middlename = $exp[ 2 ] ?? '-';
                        }

                        $ticket->created_at = Carbon::parse( $r->time_created )->toDateTimeString();
                        $ticket->updated_at = Carbon::parse( $r->time_updated )->toDateTimeString();
                        $ticket->id = $r->id;
                        $ticket->region_id = 1;
                        if ( $first_history && $first_history->user_id && User::find( $first_history->user_id ) )
                        {
                            $ticket->author_id = $first_history->user_id;
                        }
                        else
                        {
                            $ticket->author_id = $admin_id;
                        }

                        $ticket->status_code = $status_code;
                        $ticket->status_name = Ticket::$statuses[ $status_code ];

                        $ticket->deadline_acceptance = Carbon::parse( $r->time_accept_before )->toDateTimeString();
                        $ticket->deadline_execution = Carbon::parse( $r->time_execute_before )->toDateTimeString();

                        if ( $r->rating > 0 )
                        {
                            $ticket->rate = $r->rating;
                        }

                        if ( ! empty( $r->rating_comment ) )
                        {
                            $ticket->rate_comment = $r->rating_comment;
                        }

                        $ticket->save();

                        $ticketManagement = new TicketManagement;
                        $ticketManagement->ticket_id = $ticket->id;
                        $ticketManagement->management_id = $r->division_id ?? $r->company_id;
                        $ticketManagement->created_at = Carbon::parse( $r->time_created )->toDateTimeString();
                        $ticketManagement->updated_at = Carbon::parse( $r->time_updated )->toDateTimeString();
                        $ticketManagement->status_code = $ticket->status_code;
                        $ticketManagement->status_name = $ticket->status_name;
                        $ticketManagement->save();

                        foreach ( $data->data->uploads as $s )
                        {
                            if ( ! $s->url ) continue;
                            $file_contents = @file_get_contents( $s->url );
                            if ( empty( $file_contents ) ) continue;
                            $exp = explode( '.', $s->filename );
                            $file_name = $s->original_filename . '.' . $exp[ 1 ];
                            if ( empty( $file_name ) ) continue;
                            $file_path = 'files/' . Str::random(40);
                            if ( ! Storage::put( $file_path, $file_contents ) ) continue;
                            if ( empty( $file_path ) ) continue;
                            $file = new File;
                            $file->fill([
                                'model_id'      => $ticketManagement->id,
                                'model_name'    => get_class( $ticketManagement ),
                                'path'          => $file_path,
                                'name'          => $file_name
                            ]);
                            if ( $s->user_id && User::find( $s->user_id ) )
                            {
                                $file->author_id = $s->user_id;
                            }
                            if ( ! $file->author_id )
                            {
                                $file->author_id = $admin_id;
                            }
                            $file->created_at = Carbon::parse( $s->time_created )->toDateTimeString();
                            $file->updated_at = Carbon::parse( $s->time_updated )->toDateTimeString();
                            $file->save();
                        }

                        foreach ( $data->data->comments as $s )
                        {
                            $comment = new Comment;
                            $comment->fill([
                                'model_id'      => $ticketManagement->id,
                                'model_name'    => get_class( $ticketManagement ),
                                'text'          => $s->content
                            ]);
                            if ( $s->user_id && User::find( $s->user_id ) )
                            {
                                $comment->author_id = $s->user_id;
                            }
                            if ( ! $comment->author_id )
                            {
                                $comment->author_id = $admin_id;
                            }
                            $comment->created_at = Carbon::parse( $s->time_created )->toDateTimeString();
                            $comment->updated_at = Carbon::parse( $s->time_updated )->toDateTimeString();
                            $comment->save();
                        }

                        //break 2;

                    }

                    $this->info( 'File ' . $file . ' Complete' );

                    //break;

                }
            }

            $this->info( 'Tickets End' );
            */

            //$db->commit();
            //$db->rollBack();

        }
        catch ( \Exception $e )
        {
            dd( $e );
        }

	}

    public function info ( $string, $verbosity = NULL )
    {
        parent::info( date( 'Y-m-d H:i:s' ) . ' ' . $string, $verbosity );
    }

}