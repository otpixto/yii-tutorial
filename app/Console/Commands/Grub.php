<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\ManagementAct;
use App\Models\Type;
use App\Models\Work;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use App\Models\Comment;
use App\Models\File;
use App\Models\Ticket;
use App\Models\TicketManagement;
use App\Models\Executor;
use App\Models\Building;
use App\Models\Customer;
use App\Models\Segment;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Grub extends Command
{

    protected $signature = 'grub:verin';

    protected $description = 'Спиздить все данные у ЕДС Электросталь';

    public function __construct ()
    {
        ini_set( 'memory_limit', '-1' );
        set_time_limit( 0 );
        $this->client = new Client();
        parent::__construct();
    }

    public function handle ()
    {

        /*$users = User
            ::whereDoesntHave( 'managements' )
            ->whereDoesntHave( 'roles' )
            ->whereDoesntHave( 'tickets' )
            ->whereDoesntHave( 'works' )
            ->whereNull( 'email' )
            ->get();

        foreach ( $users as $user )
        {
            $user->delete();
        }*/

        /*$comments = Comment::where( 'model_name', '=', 'App\Models\TicketManagement' )->get();
        foreach ( $comments as $comment )
        {
            $ticket = $comment->parent->ticket;
            $comment->model_name = get_class( $ticket );
            $comment->model_id = $ticket->id;
            $comment->save();
        }*/

        /*$acts = ManagementAct::get();
        foreach ( $acts as $act )
        {
            if ( preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $act->content, $matches ) )
            {
                $url = 'https://mo.i-eds.ru' . $matches[ 1 ];
                $act->content = str_replace( $matches[ 1 ], '/storage/acts' . $matches[ 1 ], $act->content );
                $act->save();
                Storage::disk( 'public' )->put( 'acts/' . $matches[ 1 ], file_get_contents( $url ) );
            }
        }

        die;*/

        /*$executors = Executor
            ::whereDoesntHave( 'tickets' )
            ->whereDoesntHave( 'works' )
            ->get();
        foreach ( $executors as $executor )
        {
            $executor->forceDelete();
        }*/

        /*$buildings = Building::whereNull( 'home' )->get();
        foreach ( $buildings as $building )
        {
            if ( preg_match( '/д\.([0-9].*)$/i', $building->name, $matches ) )
            {
                $building->home = $matches[ 1 ];
                $building->save();
            }
        }*/

        /*$buildings = Building::get();
        foreach ( $buildings as $building )
        {
            $fullName = $building->getFullName();
            if ( $building->genHash( $building->name ) != $building->genHash( $fullName ) )
            {
                echo $building->name . ' | ' . $fullName . PHP_EOL;
            }
        }*/

        /*$works = Work::whereNull( 'executor_id' )->get();
        foreach ( $works as $work )
        {
            $executor = Executor
                ::where( 'name', 'like', '%' . $work->who . '%' )
                ->where( 'management_id', '=', $work->management_id )
                ->first();
            if ( $executor )
            {
                $work->executor_id = $executor->id;
                $work->save();
            }
        }

        return;*/
		
		/*$tickets = Ticket
			::whereNotNull( 'customer_id' )
			->where( function ( $q )
            {
                return $q
                    ->whereNull( 'actual_building_id' )
                    ->orWhereNull( 'actual_flat' );
            })
			->get();
		$bar = $this->output->createProgressBar($tickets->count());
        foreach ( $tickets as $ticket )
        {
			$bar->advance();
			$customer = Customer::withTrashed()->find( $ticket->customer_id );
			if ( $customer )
            {
                $ticket->actual_building_id = $customer->actual_building_id;
                $ticket->actual_flat = $customer->actual_flat;
                $ticket->save();
            }
        }
		$bar->finish();

        return;*/

        $api_url = 'https://mo.i-eds.ru/api/';

        $headers = [
            'Authorization'         => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiIwYzY5MDNmMi0yOWU3LTQyNmMtOTI4Zi0yYWQzZjk0MWQ2MjciLCJzdWIiOiJtdmVyaW5AbWFpbC5ydSIsInR5cGUiOiJlbWFpbCIsImlhdCI6MTUzMDAyMDMxOCwianRpIjoiMDk4Y2VmYTc4MGRlMDUzYmE1YWJhMjQ1YTE1MDgyZTY2N2U3ZjBiNyJ9.ENhadqF_iqEfaPPCZJ7h23OJTO7jJa9tZXJH-cy5lRE',
        ];

        $this->info( 'Types Start' );

        $url = $api_url . 'service_qualifiers';

        $response = $this->client->get( $url, [
            'headers' => $headers
        ]);

        $json_string = $response->getBody();
        $types = json_decode( $json_string );
        $bar = $this->output->createProgressBar(count($types->data));
        foreach ( $types->data as $_type )
        {

            $bar->advance();

            $category = Category
                ::withTrashed()
                ->find( $_type->service_qualifier_id );
            if ( ! $category )
            {
                $category = new Category;
                $category->id = $_type->service_qualifier_id;
                $code = str_replace( '-', '.', $_type->code );
                $exp = explode( '.', $code );
                foreach ( $exp as & $e )
                {
                    if ( mb_strlen( $e ) < 2 )
                    {
                        $e = '0' . $e;
                    }
                }
                $code = implode( '.', $exp );
                $category->name = $code . '. ' . $_type->title;
            }

            $category->emergency = $_type->is_emergency ? 1 : 0;
            $category->period_acceptance = $_type->accept_time;
            $category->period_execution = $_type->execution_time;
            $category->need_act = $_type->is_act_required ? 1 : 0;
            $category->save();

            $type = Type
                ::withTrashed()
                ->find( $_type->service_qualifier_id );
            if ( ! $type )
            {
                $type = new Type();
                $type->id = $_type->service_qualifier_id;
                $code = str_replace( '-', '.', $_type->code );
                $exp = explode( '.', $code );
                foreach ( $exp as & $e )
                {
                    if ( mb_strlen( $e ) < 2 )
                    {
                        $e = '0' . $e;
                    }
                }
                $code = implode( '.', $exp );
                $type->name = $code . '. ' . $_type->title;
                $type->category_id = $category->id;
            }
            $type->description = $_type->specific_questions;
            $type->season = $_type->gzhi_seasonality;
            $type->emergency = $_type->is_emergency ? 1 : 0;
            $type->period_acceptance = $_type->accept_time;
            $type->period_execution = $_type->execution_time;
            $type->guid = $_type->gzhi_guid;
            $type->need_act = $_type->is_act_required ? 1 : 0;
            $type->save();
            foreach ( $_type->children as $c )
            {
                $type = Type
                    ::withTrashed()
                    ->find( $c->service_qualifier_id );
                if ( ! $type )
                {
                    $type = new Type();
                    $type->id = $c->service_qualifier_id;
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
                }
                $type->description = $c->specific_questions;
                $type->season = $c->gzhi_seasonality;
                $type->emergency = $c->is_emergency ? 1 : 0;
                $type->period_acceptance = $c->accept_time;
                $type->period_execution = $c->execution_time;
                $type->guid = $c->gzhi_guid;
                $type->need_act = $c->is_act_required ? 1 : 0;
                $type->save();
                foreach ( $c->children as $c2 )
                {
                    $type = Type
                        ::withTrashed()
                        ->find( $c2->service_qualifier_id );
                    if ( ! $type )
                    {
                        $type = new Type();
                        $type->id = $c2->service_qualifier_id;
                        $code = str_replace( '-', '.', $c2->code );
                        $exp = explode( '.', $code );
                        foreach ( $exp as & $e )
                        {
                            if ( mb_strlen( $e ) < 2 )
                            {
                                $e = '0' . $e;
                            }
                        }
                        $type->name = $code . '. ' . $c2->title;
                        $type->category_id = $category->id;
                    }
                    $type->description = $c2->specific_questions;
                    $type->season = $c2->gzhi_seasonality;
                    $type->emergency = $c2->is_emergency ? 1 : 0;
                    $type->period_acceptance = $c2->accept_time;
                    $type->period_execution = $c2->execution_time;
                    $type->guid = $c2->gzhi_guid;
                    $type->need_act = $c2->is_act_required ? 1 : 0;
                    $type->save();
                }
            }
        }

        $bar->finish();

        $this->info( 'Types End' );

        /*
        $this->info( 'Regions Start' );

        $url = $api_url . 'regions?sort=id';

        $response = $this->client->get( $url, [
            'headers' => $headers
        ]);

        $json_string = $response->getBody();
        $regions = json_decode( $json_string );
        foreach ( $regions->data as $region )
        {
            $segment = Segment::find( $region->id );
            if ( ! $segment )
            {
                $segment = new Segment;
                $segment->id = $region->id;
            }
            $segment->name = $region->title;
            $segment->type_id = 1;
            $segment->save();
        }

        $this->info( 'Regions End' );

        $this->info( 'Districts Start' );

        $url = $api_url . 'districts?sort=id';

        $response = $this->client->get( $url, [
            'headers' => $headers
        ]);

        $json_string = $response->getBody();
        $districts = json_decode( $json_string );
        foreach ( $districts->data as $district )
        {
            $segment = Segment::find( $district->id );
            if ( ! $segment )
            {
                $segment = new Segment;
                $segment->id = $district->id;
            }
            $segment->name = $district->title;
            $segment->type_id = 2;
            $segment->parent_id = $district->region_id;
            $segment->save();
        }

        $this->info( 'Districts End' );

        $this->info( 'Cities Start' );

        $url = $api_url . 'cities?sort=id';

        $response = $this->client->get( $url, [
            'headers' => $headers
        ]);

        $json_string = $response->getBody();
        $cities = json_decode( $json_string );
        foreach ( $cities->data as $city )
        {
            $segment = Segment::find( $city->id );
            if ( ! $segment )
            {
                $segment = new Segment;
                $segment->id = $city->id;
            }
            $segment->name = $city->title;
            $segment->type_id = 3;
            $segment->parent_id = $city->district_id ?: $city->region_id;
            $segment->save();
        }

        $this->info( 'Cities End' );

        $this->info( 'Streets Start' );

        $url = $api_url . 'streets?sort=id';

        $response = $this->client->get( $url, [
            'headers' => $headers
        ]);

        $json_string = $response->getBody();
        $streets = json_decode( $json_string );
        foreach ( $streets->data as $street )
        {
            if ( empty( $street->city_id ) ) continue;
            $segment = Segment::find( $street->id );
            if ( ! $segment )
            {
                $segment = new Segment;
                $segment->id = $street->id;
            }
            $segment->name = $street->title;
            $segment->type_id = 4;
            $segment->parent_id = $street->city_id;
            $segment->save();
        }

        $this->info( 'Streets End' );

        $this->info( 'Buildings Start' );

        $url = $api_url . 'buildings';

        $response = $this->client->get( $url, [
            'headers' => $headers
        ]);

        $json_string = $response->getBody();
        $buildings = json_decode( $json_string );
        foreach ( $buildings->data as $building )
        {
            $address = Building::find( $building->building_id );
            if ( ! $address )
            {
                $address = new Building;
                $address->id = $building->building_id;
            }
            $address->name = $building->full_address;
            $address->hash = Building::genHash( $address->name );
            $address->segment_id = $building->street_id ?: $building->city_id ?: $building->district_id ?: $building->region_id;
            $address->guid = $building->gzhi_guid;
            $address->lon = $building->longitude;
            $address->lat = $building->latitude;
            $address->date_of_construction = $building->date_of_construction;
            $address->building_type_id = $building->building_type_id;
            $address->eirts_number = $building->eirts_number;
            $address->total_area = $building->total_area;
            $address->living_area = $building->living_area;
            $address->floor_count = $building->floor_count;
            $address->porches_count = $building->porches_count;
            $address->room_total_count = $building->room_total_count;
            $address->room_living_count = $building->room_living_count;
            $address->room_mask = $building->room_mask;
            $address->is_first_floor_living = $building->is_first_floor_living ? 1 : 0;
            $address->first_floor_index = $building->first_floor_index;
            $address->save();
        }

        $this->info( 'Buildings End' );

        die;
        */

        /*
        $this->info( 'Acts Start' );

        $url = $api_url . 'acts';

        $response = $this->client->get( $url, [
            'headers' => $headers
        ]);

        $json_string = $response->getBody();
        $acts = json_decode( $json_string );
        foreach ( $acts->data as $_act )
        {
            $act = ManagementAct::find( $_act->act_id );
            if ( ! $act )
            {
                $act = new ManagementAct();
                $act->id = $_act->act_id;
                $act->management_id = $_act->company_id;
            }
            $act->name = $_act->title;
            $act->content = $_act->content;
            $act->created_at = Carbon::parse( $_act->time_created )->toDateTimeString();
            $act->updated_at = Carbon::parse( $_act->time_updated )->toDateTimeString();
            $act->save();
        }

        $this->info( 'Acts End' );

        die;
        */

        /*$this->info( 'Managements' );

        $url = $api_url . 'companies';

        $response = $this->client->get( $url, [
            'headers' => $headers
        ]);

        $json_string = $response->getBody();
        file_put_contents( storage_path( 'json/managements.json' ), $json_string );

        $this->info( 'Complete' );*/

		/*
        $this->info( 'Users Start' );
        $page = 0;
        $pages = null;

        while ( is_null( $pages ) || $pages > $page )
        {

            $this->info( 'Users Page #' . $page . ' Start' );

            $url = $api_url . 'users?pn=' . $page . '&ps=' . $per_page . '&sort=-id';

            $response = $this->client->get( $url, [
                'headers' => $headers
            ]);

            $pages = (int) $response->getHeader( 'X-PAGINATION-PAGE-COUNT' )[ 0 ] ?? 0;

            $json_string = $response->getBody();
            file_put_contents( storage_path( 'json/users/' . $page . '.json' ), $json_string );

            $this->info( 'Users Page #' . $page . ' Complete' );

            $page ++;

        }

        $this->info( 'Users End' );
		*/
		
		/*$this->info( 'Customers Start' );
		
		$page = 0;
        $pages = null;
        $per_page = 100;
        $max_pages = 30;

        while ( is_null( $pages ) || ( $pages > $page && $page < $max_pages ) )
		{
			
			$url = $api_url . 'clients/table?pn=' . $page . '&ps=' . $per_page . '&sort=-time_updated';

			$response = $this->client->get( $url, [
				'headers' => $headers
			]);
			
			$pages = (int)$response->getHeader('X-PAGINATION-PAGE-COUNT')[0] ?? 0;

            $this->info('Customers Page #' . $page . '/'. $pages . ' Start');

			$json_string = $response->getBody();
			$customers = json_decode( $json_string );
			
			$bar = $this->output->createProgressBar(count($customers->data));
			
			foreach ( $customers->data as $_customer )
			{
				$bar->advance();
				$customer = Customer
                    ::withTrashed()
                    ->find( $_customer->user_id );
				if ( ! $customer )
				{
					$customer = new Customer;
					$customer->id = $_customer->user_id;
					$customer->provider_id = 1;
					$customer->created_at = Carbon::now()->toDateTimeString();
					$customer->updated_at = $customer->created_at;
				}
				else
                {
                    $customer->deleted_at = null;
                }
				if ( $_customer->created_by->deleted )
				{
					$customer->deleted_at = $customer->updated_at;
				}
				$customer->firstname = $_customer->first_name;
				$customer->middlename = $_customer->middle_name;
				$customer->lastname = $_customer->last_name;
				$customer->email = $_customer->email ?: null;
				$customer->phone = mb_substr( $_customer->phone, -10 ) ?: null;
				if ( ! empty( $_customer->phones ) )
				{
					if ( ! $customer->phone && isset( $_customer->phones[ 0 ] ) )
					{
						$customer->phone = mb_substr( $_customer->phones[ 0 ]->phone_number, -10 );
					}
					if ( isset( $_customer->phones[ 1 ] ) )
					{
						$customer->phone2 = mb_substr( $_customer->phones[ 1 ]->phone_number, -10 );
					}
				}
				if ( ( ! $customer->actual_building_id || ! $customer->actual_flat ) && ! empty( $_customer->rooms ) )
				{
					$url = $api_url . 'rooms/' . $_customer->rooms[ 0 ]->room_id;
					$response = $this->client->get( $url, [
						'headers' => $headers
					]);
					$json_string = $response->getBody();
					$room = json_decode( $json_string );
					if ( ! empty( $room ) && ! empty( $room->data ) )
					{
						$room = $room->data;
						$_building = $room->building;
						$building = Building
                            ::withTrashed()
                            ->find( $_building->building_id );
						if ( ! $building )
						{
							$building = new Building;
							$building->id = $_building->building_id;
							$building->provider_id = 1;
						}
						$building->name = $_building->full_address;
						$building->hash = Building::genHash( $building->name );
						$segment_id = $_building->street_id ?: $_building->city_id ?: $_building->district_id ?: $_building->region_id;
						if ( Segment::withTrashed()->find( $segment_id ) )
						{
							$building->segment_id = $segment_id;
						}
						$building->guid = $_building->gzhi_guid;
						$building->lon = $_building->longitude;
						$building->lat = $_building->latitude;
						$building->date_of_construction = $_building->date_of_construction;
						$building->building_type_id = $_building->building_type_id;
						$building->eirts_number = $_building->eirts_number;
						$building->total_area = $_building->total_area;
						$building->living_area = $_building->living_area;
						$building->floor_count = $_building->floor_count;
						$building->porches_count = $_building->porches_count;
						$building->room_total_count = $_building->room_total_count;
						$building->room_living_count = $_building->room_living_count;
						$building->room_mask = $_building->room_mask;
						$building->is_first_floor_living = $_building->is_first_floor_living ? 1 : 0;
						$building->first_floor_index = $_building->first_floor_index;
						$building->save();
						$customer->actual_building_id = $building->id;
						$customer->actual_flat = $room->room_number;
					}
				}
				$customer->save();
			}
			
			$bar->finish();
			
			$this->info('Customers Page #' . $page . ' Complete');
			
			$page ++;
			
		}

        $this->info( 'Customers End' );
		
		*/

        $admin_id = 1;

        $this->info( 'Works Start' );
        $page = 0;
        $pages = null;
        $per_page = 100;
        $max_pages = 30;

        while ( is_null( $pages ) || ( $pages > $page && $page < $max_pages ) )
        {

            $url = $api_url . 'announcements?pn=' . $page . '&ps=' . $per_page . '&sort=-time_updated';

            $response = $this->client->get( $url, [
                'headers' => $headers
            ]);

            $pages = (int)$response->getHeader('X-PAGINATION-PAGE-COUNT')[0] ?? 0;

            $this->info('Works Page #' . $page . '/'. $pages . ' Start');

            $json_string = $response->getBody();
            $works = json_decode( $json_string );

            $bar = $this->output->createProgressBar(count($works->data));

            foreach ( $works->data as $_work )
            {

                $bar->advance();

                if ( ! $_work->division_id && ! $_work->company_id ) continue;
                if ( ! $_work->service_qualifier_id ) continue;

                $type_id = $_work->service_qualifier_id;
                if ( $type_id == -1 )
                {
                    $type_id = 71;
                }

                $type = Type
                    ::withTrashed()
                    ->find( $type_id );
                if ( ! $type ) continue;

                $work = Work
                    ::withTrashed()
                    ->find( $_work->id );
                if ( ! $work )
                {
                    $work = new Work;
                }

                $management_id = $_work->division_id ?? $_work->company_id;

                $work->fill([
                    'provider_id'       => 1,
                    'category_id'       => $type->category_id,
                    'management_id'     => $management_id,
                    'reason'            => $_work->client_description,
                    'composition'       => $_work->description,
                    'time_begin'        => Carbon::parse( $_work->time_begin )->toDateTimeString(),
                    'time_end'          => Carbon::parse( $_work->time_end )->toDateTimeString(),
                ]);
                if ( ! $_work->active )
                {
                    $work->time_end_fact = Carbon::now()->subDay()->setTime( 0, 0, 0 )->toDateTimeString();
                }
                else
                {
                    $work->time_end_fact = null;
                }
                $work->created_at = Carbon::parse( $_work->time_created )->toDateTimeString();
                $work->updated_at = Carbon::parse( $_work->time_updated )->toDateTimeString();

                if ( $_work->created_by )
                {
                    $user = User::withTrashed()->find( $_work->created_by );
                    if ( $user )
                    {
                        $work->author_id = $user->id;
                        $user->deleted_at = null;
                        $user->save();
                    }
                }

                if ( ! $work->author_id )
                {
                    $work->author_id = $admin_id;
                }

                if ( ! empty( $_work->responsible->responsible_name ) )
                {
                    $executor_name = $_work->responsible->responsible_name;
                    $executor = Executor
                        ::withTrashed()
                        ->whereRaw( 'REPLACE( name, \' \', \'\' ) like ?', [ '%' . str_replace( ' ', '', $executor_name ) . '%' ] )
                        ->where( 'management_id', '=', $management_id )
                        ->first();
                    if ( ! $executor )
                    {
                        $executor = new Executor;
                        $executor->management_id = $management_id;
                        $executor->name = $executor_name;
                    }
                    $work->executor_id = $executor->id;
                    if ( ! empty( $_work->responsible->responsible_phone ) )
                    {
                        $executor->phone = $work->phone;
                    }
                    $executor->save();
                }

                $work->save();

                $work->logs()->forceDelete();

                $ids = [];
                foreach ( $_work->buildings as $building )
                {
                    $address = Building
                        ::withTrashed()
                        ->search( $building->full_address )
                        ->first();
                    if ( ! $address )
                    {
                        $address = Building
                            ::withTrashed()
                            ->find( $building->id );
                        if ( ! $address )
                        {
                            $address = new Building;
                            $address->id = $building->id;
                            $address->name = $building->full_address;
                            $address->save();
                        }
                    }
                    $ids[] = $address->id;
                }
                $work->buildings()->sync( $ids );

            }
			
			$bar->finish();

            $this->info( 'Works Page #' . $page . ' Complete' );

            $page ++;

        }

        $this->info( 'Works End' );

        \Cache::tags( 'works_counts' )->flush();

        $admin_id = 1;

        $this->info( 'Tickets Start' );
        $page = 0;
        $pages = null;
		$per_page = 100;
        $max_pages = 30;

		#\DB::connection( 'eds_verin' )->table( 'comments' )->delete();
		#\DB::connection( 'eds_verin' )->table( 'files' )->delete();
		#\DB::connection( 'eds_verin' )->table( 'tickets' )->where( 'status_code', '=', 'draft' )->delete();

		$statuses = [
			2 => 'transferred',
			3 => 'in_process',
			5 => 'accepted',
			6 => 'assigned',
			8 => 'rejected',
			10 => 'completed_with_act',
			11 => 'closed_with_confirm',
			14 => 'transferred_again',
			15 => 'from_lk',
			16 => 'cancel',
		];

        while ( is_null( $pages ) || ( $pages > $page && $page < $max_pages ) )
        {

            $url = $api_url . 'issues?pn=' . $page . '&ps=' . $per_page . '&sort=-time_updated';

            $response = $this->client->get( $url, [
                'headers' => $headers
            ]);

            $pages = (int) $response->getHeader( 'X-PAGINATION-PAGE-COUNT' )[ 0 ] ?? 0;

            $this->info('Tickets Page #' . $page . '/'. $pages . ' Start');

            $json_string = $response->getBody();
			$tickets = json_decode( $json_string );
			
			$bar = $this->output->createProgressBar( count( $tickets->data ) );
			
			foreach ( $tickets->data as $_ticket )
			{
				
				$bar->advance();
				
				if ( ! $_ticket->division_id && ! $_ticket->company_id ) continue;
				if ( ! $_ticket->service_qualifier_id ) continue;
				
				$type_id = $_ticket->service_qualifier_id;
				if ( $type_id == -1 )
				{
					$type_id = 71;
				}

				switch ( $_ticket->status )
				{
					case 7:
						if ( $_ticket->act_id )
						{
							$status_code = 'completed_with_act';
						}
						else
						{
							$status_code = 'completed_without_act';
						}
						break;
					case 11:
						if ( $_ticket->rating && $_ticket->rating != -1 )
						{
							$status_code = 'closed_with_confirm';
						}
						else
						{
							$status_code = 'closed_without_confirm';
						}
						break;
					default:
					    if ( $_ticket->is_postponed )
                        {
                            $status_code = 'waiting';
                        }
						else if ( isset( $statuses[ $_ticket->status ] ) )
						{
							$status_code = $statuses[ $_ticket->status ];
						}
						else
						{
							continue;
						}
						break;
				}
				
				$address = Building::find( $_ticket->building_id );
				if ( ! $address ) continue;

                $url = $api_url . 'issues/' . $_ticket->id;

                $response = $this->client->get( $url, [
                    'headers' => $headers
                ]);

                $json_string = $response->getBody();
                if ( empty( $json_string ) ) continue;
                $data = json_decode( $json_string );

                $response = $this->client->get( $api_url . 'issues/history/' . $_ticket->id, [
                    'headers' => $headers
                ]);

                $json_string = $response->getBody();
                if ( empty( $json_string ) ) continue;
                $history = json_decode( $json_string );

                $first_history = end( $history->data );
				
				$ticket = Ticket
                    ::withTrashed()
                    ->find( $_ticket->id );
				if ( ! $ticket )
				{
					$ticket = new Ticket;
					$ticket->created_at = Carbon::parse( $_ticket->time_created )->toDateTimeString();
					$ticket->id = $_ticket->id;
					$ticket->provider_id = 1;
					$customer = Customer
                        ::withTrashed()
                        ->find( $_ticket->client_id );
					if ( $customer )
					{
						$ticket->customer_id = $customer->id;
						$ticket->lastname = $customer->lastname;
						$ticket->firstname = $customer->firstname;
						$ticket->middlename = $customer->middlename;
						$ticket->actual_building_id = $customer->actual_building_id;
						$ticket->actual_flat = $customer->actual_flat;
					}
					else
					{
						$exp = explode( ' ', $_ticket->client_name );
						$ticket->lastname = $exp[ 0 ] ?? '-';
						$ticket->firstname = $exp[ 1 ] ?? '-';
						$ticket->middlename = $exp[ 2 ] ?? '-';
					}
					$ticketManagement = new TicketManagement;
					$ticketManagement->ticket_id = $ticket->id;
					$ticketManagement->created_at = Carbon::parse( $_ticket->time_created )->toDateTimeString();
				}
				else
				{
					$ticketManagement = $ticket->managements()->first();
					if ( ! $ticketManagement ) continue;
				}

				$ticket->deleted_at = null;
				$ticket->updated_at = Carbon::parse( $_ticket->time_updated )->toDateTimeString();
				$ticket->transferred_at = Carbon::parse( $_ticket->time_created )->toDateTimeString();

				if ( ! empty( $data->data->time_accepted ) )
                {
                    $ticket->accepted_at = Carbon::parse( $data->data->time_accepted )->toDateTimeString();
                }

                if ( ! empty( $data->data->time_completed ) )
                {
                    $ticket->completed_at = Carbon::parse( $data->data->time_completed )->toDateTimeString();
                }

				$ticket->fill([
					'urgently' => $_ticket->is_urgent ? 1 : 0,
					'emergency' => $_ticket->is_emergency ? 1 : 0,
					'dobrodel' => $_ticket->is_goodmaker ? 1 : 0,
					'type_id' => $type_id,
					'building_id' => $_ticket->building_id,
					'flat' => $data->data->room_number,
					'place_id' => 2,
					'text' => $_ticket->description,
					'phone' => mb_substr( $_ticket->client_phone, -10 )
				]);
				
				$ticket->status_code = $status_code;
				$ticket->status_name = Ticket::$statuses[ $status_code ];

				$ticket->deadline_acceptance = Carbon::parse( $_ticket->time_accept_before )->toDateTimeString();
				$ticket->deadline_execution = Carbon::parse( $_ticket->time_execute_before )->toDateTimeString();
				
				$ticketManagement->management_id = $_ticket->division_id ?? $_ticket->company_id;
				$ticketManagement->updated_at = Carbon::parse( $_ticket->time_updated )->toDateTimeString();
				
				if ( $_ticket->rating > 0 )
				{
					$ticket->rate = $_ticket->rating;
					$ticketManagement->rate = $_ticket->rating;
				}

				if ( ! empty( $_ticket->rating_comment ) )
				{
					$ticket->rate_comment = $_ticket->rating_comment;
					$ticketManagement->rate_comment = $_ticket->rating_comment;
				}
				
				if ( ! empty( $_ticket->executor_id ) && ! empty( $_ticket->executor_name ) )
				{
					$executor = Executor
                        ::withTrashed()
                        ->find( $_ticket->executor_id );
					if ( ! $executor )
					{
						$executor = new Executor;
						$executor->fill([
							'management_id'		=> $ticketManagement->management_id,
							'name'				=> $_ticket->executor_name
						]);
						$executor->id = $_ticket->executor_id;
						$executor->save();
					}
					$ticketManagement->executor_id = $_ticket->executor_id;
					if ( $ticket->status_code == 'transferred' )
					{
						$ticket->status_code = 'assigned';
						$ticket->status_name = Ticket::$statuses[ 'assigned' ];
					}
				}
				
				if ( $first_history && $first_history->user_id )
				{
                    $user = User
                        ::withTrashed()
                        ->find( $first_history->user_id );
                    if ( $user )
                    {
                        $ticket->author_id = $user->id;
                        $user->deleted_at = null;
                        $user->save();
                    }
				}
				else
				{
					$ticket->author_id = $admin_id;
				}

                if ( $ticket->status_code == 'waiting' && ! empty( $_ticket->postponed_comment ) )
                {
                    $ticket->postponed_comment = $_ticket->postponed_comment;
                }

                if ( ! empty( $_ticket->time_scheduled_begin ) )
                {
                    $ticketManagement->scheduled_begin = Carbon::parse( $_ticket->time_scheduled_begin )->toDateTimeString ();
                    if ( $ticket->status_code == 'waiting' )
                    {
                        $ticket->postponed_to = Carbon::parse( $_ticket->time_scheduled_begin )->toDateTimeString ();
                    }
                }

                if ( ! empty( $_ticket->time_scheduled_end ) )
                {
                    $ticketManagement->scheduled_end = Carbon::parse( $_ticket->time_scheduled_end )->toDateTimeString ();
                }
				
				$ticketManagement->status_code = $ticket->status_code;
				$ticketManagement->status_name = $ticket->status_name;

				if ( $_ticket->act_id )
                {
                    $ticketManagement->act_id = $_ticket->act_id;
                }

                $ticket->save();
                $ticketManagement->save();

                $ticket->logs()->forceDelete();
                $ticket->tags()->delete();
                $ticketManagement->logs()->forceDelete();
                $ticketManagement->statusesHistory()->forceDelete();

				foreach ( $data->data->uploads as $s )
				{
					if ( ! $s->url ) continue;
                    if ( File::withTrashed()->find( $s->upload_id ) ) continue;
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
                    $file->id = $s->upload_id;
					$file->created_at = Carbon::parse( $s->time_created )->toDateTimeString();
					$file->updated_at = Carbon::parse( $s->time_updated )->toDateTimeString();
					$file->save();
				}
				
				foreach ( $data->data->comments as $s )
				{
				    $comment = Comment::withTrashed()->find( $s->comment_id );
				    if ( ! $comment )
                    {
                        $comment = new Comment;
                        $comment->id = $s->comment_id;
                    }
					$comment->fill([
						'model_id'      => $ticket->id,
						'model_name'    => get_class( $ticket ),
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
                    $comment->deleted_at = null;
					$comment->created_at = Carbon::parse( $s->time_created )->toDateTimeString();
					$comment->updated_at = Carbon::parse( $s->time_updated )->toDateTimeString();
					$comment->save();
				}
				
			}
			
			$bar->finish();
			
            //file_put_contents( storage_path( 'json/tickets/' . $page . '.json' ), $json_string );

            $this->info( 'Tickets Page #' . $page . ' Complete' );

            $page ++;

        }
		
		$this->info( 'Tickets End' );

        \Cache::tags( 'tickets_counts' )->flush();

    }
	
	public function info ( $string, $verbosity = NULL )
	{
		parent::info( date( 'Y-m-d H:i:s' ) . ' ' . $string, $verbosity );
	}

}
