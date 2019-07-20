<?php

namespace App\Jobs;

use App\Models\Asterisk\Cdr;
use App\Models\Report;
use App\Models\TicketManagement;
use App\Models\StatusHistory;
use App\Models\Work;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $report;
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct ( Report $report, User $user )
    {
        $this->report = $report;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle ()
    {

        $logs = new Logger( 'REST' );
        $logs->pushHandler( new StreamHandler( storage_path( 'logs/job_report.log' ) ) );

        try
        {

            \Auth::login( $this->user );

            $logs->addInfo( 'Пользователь', [ \Auth::user() ] );
            $logs->addInfo( 'Отчет', [ $this->report ] );


            $date_from = $this->report->date_from;
            $date_to = $this->report->date_to;

            $logs->addInfo( 'Период', [ $date_from, $date_to ] );

            $diff_hours = $date_from->diffInHours( $date_to );
            $date_prev_from = ( clone $date_from )->subHours( $diff_hours );

            $ticketManagements = TicketManagement
                ::mine()
                ->whereBetween( 'created_at', [ $date_prev_from, $date_to ] )
                ->whereNotIn( 'status_code', [ 'draft', 'moderate', 'created', 'no_contract', 'rejected_operator' ] )
                ->get();
				
			$ticketManagementStatuses = StatusHistory
                ::where( 'model_name', '=', TicketManagement::class )
                ->whereBetween( 'created_at', [ $date_from, $date_to ] )
                ->where( 'status_code', '=', 'transferred_again' )
                ->get();

            $works = Work
                ::whereHas( 'managements', function ( $managements )
                {
                    return $managements
                        ->mine()
                        ->where( 'category_id', '!=', 1 );
                })
                ->whereBetween( \DB::raw( 'DATE( time_begin )' ), [ $date_from, $date_to ] )
                ->get();

            $data = [
                'calls' => 0,
                'works' => [],
                'current'=> [
                    'tickets' => 0,
                    'statuses' => [
                        'uk'=> [
                            'total'             => [ 0, 100, 0 ],
                            'completed'         => [ 0, 0, 0 ],
                            'in_process'        => [ 0, 0, 0 ],
                            'cancel'            => [ 0, 0, 0 ],
                            'waiting'           => [ 0, 0, 0 ],
                            'expired'           => [ 0, 0, 0 ],
                        ],
                        'rso'=> [
                            'total'             => [ 0, 100, 0 ],
                            'completed'         => [ 0, 0, 0 ],
                            'in_process'        => [ 0, 0, 0 ],
                            'cancel'            => [ 0, 0, 0 ],
                            'waiting'           => [ 0, 0, 0 ],
                            'expired'           => [ 0, 0, 0 ],
                        ],
                    ],
                    'types' => [],
                    'parents' => [],
                    'managements' => [],
                ],
                'prev'=> [
                    'tickets' => 0,
                    'statuses' => [
                        'uk'=> [
                            'total'             => [ 0, 100, 0 ],
                            'completed'         => [ 0, 0, 0 ],
                            'in_process'        => [ 0, 0, 0 ],
                            'cancel'            => [ 0, 0, 0 ],
                            'waiting'           => [ 0, 0, 0 ],
                            'expired'           => [ 0, 0, 0 ],
                        ],
                        'rso'=> [
                            'total'             => [ 0, 100, 0 ],
                            'completed'         => [ 0, 0, 0 ],
                            'in_process'        => [ 0, 0, 0 ],
                            'cancel'            => [ 0, 0, 0 ],
                            'waiting'           => [ 0, 0, 0 ],
                            'expired'           => [ 0, 0, 0 ],
                        ],
                    ],
                    'types' => [],
                ],
            ];

            foreach ( $works as $work )
            {
                foreach ( $work->managements as $management )
                {
                    if ( ! isset( $data[ 'works' ][ $management->name ] ) )
                    {
                        $data[ 'works' ][ $management->name ] = [ 0, 0 ];
                    }
                    if ( $work->type_id == 2 )
                    {
                        $data[ 'works' ][ $management->name ][ 0 ] ++;
                    }
                    else
                    {
                        $data[ 'works' ][ $management->name ][ 1 ] ++;
                    }
                }
            }

            foreach ( $ticketManagements as $ticketManagement )
            {

                if ( $ticketManagement->created_at->timestamp >= $date_from->timestamp )
                {
                    $key = 'current';
                }
                else
                {
                    $key = 'prev';
                }

                if ( $ticketManagement->management->category_id == 1 || $ticketManagement->management->category_id == 2 )
                {
                    $key2 = 'uk';
                }
                else
                {
                    $key2 = 'rso';
                }

                $data[ $key ][ 'tickets' ] ++;

                $type = $ticketManagement->ticket->type->parent->name ?? $ticketManagement->ticket->type->name;
                if ( ! isset( $data[ $key ][ 'types' ][ $type ] ) )
                {
                    $data[ $key ][ 'types' ][ $type ] = [ 0, 0, 0 ];
                }
                $data[ $key ][ 'types' ][ $type ][ 0 ] ++;

                if ( $key == 'current' )
                {

                    if ( $key2 == 'uk' )
                    {

                        $parentManagement = $ticketManagement->management->parent->name ?? $ticketManagement->management->name;
                        if ( ! isset( $data[ 'current' ][ 'parents' ][ $parentManagement ] ) )
                        {
                            $data[ 'current' ][ 'parents' ][ $parentManagement ] = [
                                'total'         => 0,
                                'avg_rate'      => [],
                                'rating'        => 0,
                                'statuses'      => [
                                    'completed'     => [ 0, 0 ],
                                    'expired'       => [ 0, 0 ],
                                    'in_process'    => [ 0, 0 ],
                                    'not_completed' => [ 0, 0 ],
                                ],

                            ];
                        }

                        $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'total' ] ++;
                        switch ( $ticketManagement->status_code )
                        {
                            case 'closed_with_confirm':
                            case 'closed_without_confirm':
                            case 'confirmation_operator':
                            case 'confirmation_client':
                            case 'not_verified':
                            case 'completed_with_act':
                            case 'completed_without_act':
                                if ( $ticketManagement->ticket->overdueDeadlineExecution() )
                                {
                                    $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'statuses' ][ 'expired' ][ 0 ] ++;
                                }
                                else
                                {
                                    $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'statuses' ][ 'completed' ][ 0 ] ++;
                                }
                                break;
                            case 'transferred':
                            case 'transferred_again':
                            case 'accepted':
                            case 'assigned':
                            case 'in_process':
                            case 'conflict':
                                $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'statuses' ][ 'in_process' ][ 0 ] ++;
                                break;
                        }

                        if ( $ticketManagement->rate )
                        {
                            $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'avg_rate' ][] = $ticketManagement->rate;
                        }

                    }

                    // БУ
                    if ( $ticketManagement->management->category_id == 6 )
                    {

                        if ( ! isset( $data[ 'current' ][ 'managements' ][ $ticketManagement->management->name ] ) )
                        {
                            $data[ 'current' ][ 'managements' ][ $ticketManagement->management->name ] = [
                                'total'             => 0,
                                'completed'         => 0,
                                'completed_percent' => 0,
                                'expired'           => 0,
                                'expired_percent'   => 0,
                                'avg_rate'          => [],
                            ];
                        }

                        if ( $ticketManagement->rate )
                        {
                            $data[ 'current' ][ 'managements' ][ $ticketManagement->management->name ][ 'avg_rate' ][] = $ticketManagement->rate;
                        }

                        $data[ 'current' ][ 'managements' ][ $ticketManagement->management->name ][ 'total' ] ++;

                        if ( $ticketManagement->ticket->overdueDeadlineExecution() )
                        {
                            $data[ 'current' ][ 'managements' ][ $ticketManagement->management->name ][ 'expired' ] ++;
                        }

                        switch ( $ticketManagement->status_code )
                        {
                            case 'closed_with_confirm':
                            case 'closed_without_confirm':
                            case 'confirmation_operator':
                            case 'confirmation_client':
                            case 'not_verified':
                            case 'completed_with_act':
                            case 'completed_without_act':
                                $data[ 'current' ][ 'managements' ][ $ticketManagement->management->name ][ 'completed' ] ++;
                                break;
                        }

                    }

                }

                $data[ $key ][ 'statuses' ][ $key2 ][ 'total' ][ 0 ] ++;

                switch ( $ticketManagement->status_code )
                {
                    case 'closed_with_confirm':
                    case 'closed_without_confirm':
                    case 'confirmation_operator':
                    case 'confirmation_client':
                    case 'not_verified':
                    case 'completed_with_act':
                    case 'completed_without_act':
                        if ( $ticketManagement->ticket->overdueDeadlineExecution() )
                        {
                            $data[ $key ][ 'statuses' ][ $key2 ][ 'expired' ][ 0 ] ++;
                        }
                        else
                        {
                            $data[ $key ][ 'statuses' ][ $key2 ][ 'completed' ][ 0 ] ++;
                        }
                        break;
                    case 'transferred':
                    case 'transferred_again':
                    case 'accepted':
                    case 'assigned':
                    case 'in_process':
                        $data[ $key ][ 'statuses' ][ $key2 ][ 'in_process' ][ 0 ] ++;
                        break;
                    case 'rejected':
                    case 'cancel':
                        $data[ $key ][ 'statuses' ][ $key2 ][ 'cancel' ][ 0 ] ++;
                        break;
                    case 'waiting':
                        $data[ $key ][ 'statuses' ][ $key2 ][ 'waiting' ][ 0 ] ++;
                        break;
                }

            }
			
			foreach ( $ticketManagementStatuses as $ticketManagementStatus )
			{
				$ticketManagement = $ticketManagementStatus->parent;
				if ( ! $ticketManagement ) continue;
				$parentManagement = $ticketManagement->management->parent->name ?? $ticketManagement->management->name;
				if ( ! isset( $data[ 'current' ][ 'parents' ][ $parentManagement ] ) ) continue;
				$data[ 'current' ][ 'parents' ][ $parentManagement ][ 'statuses' ][ 'not_completed' ][ 0 ] ++;
			}

            $data[ 'calls' ] = Cdr
                ::whereIn( \DB::raw( 'RIGHT( dst, 10 )' ), [ '8005503115', '4995503115' ] )
                ->whereBetween( 'calldate', [ $date_from, $date_to ] )
                ->count();

            foreach ( $data[ 'current' ][ 'statuses' ] as $key2 => $row )
            {
                foreach ( $row as $status => $item )
                {
                    if ( $status != 'total' )
                    {
                        $data[ 'current' ][ 'statuses' ][ $key2 ][ $status ][ 1 ] = $data[ 'current' ][ 'statuses' ][ $key2 ][ 'total' ][ 0 ] ? round( $data[ 'current' ][ 'statuses' ][ $key2 ][ $status ][ 0 ] / $data[ 'current' ][ 'statuses' ][ $key2 ][ 'total' ][ 0 ] * 100 ) : 0;
                        $data[ 'prev' ][ 'statuses' ][ $key2 ][ $status ][ 1 ] = $data[ 'prev' ][ 'statuses' ][ $key2 ][ 'total' ][ 0 ] ? round( $data[ 'prev' ][ 'statuses' ][ $key2 ][ $status ][ 0 ] / $data[ 'prev' ][ 'statuses' ][ $key2 ][ 'total' ][ 0 ] * 100 ) : 0;
                        $data[ 'current' ][ 'statuses' ][ $key2 ][ $status ][ 2 ] = round( $data[ 'current' ][ 'statuses' ][ $key2 ][ $status ][ 1 ] - $data[ 'prev' ][ 'statuses' ][ $key2 ][ $status ][ 1 ] );
                    }
                    else
                    {
                        $data[ 'current' ][ 'statuses' ][ $key2 ][ $status ][ 2 ] = ! empty( $data[ 'prev' ][ 'statuses' ][ $key2 ][ $status ][ 0 ] ) ? round( ( $data[ 'current' ][ 'statuses' ][ $key2 ][ $status ][ 0 ] / $data[ 'prev' ][ 'statuses' ][ $key2 ][ $status ][ 0 ] - 1 ) * 100 ) : 0;
                    }
                }
                uasort( $data[ 'current' ][ 'statuses' ][ $key2 ], function ( $a, $b )
                {
                    return $a[ 0 ] > $b[ 0 ] ? -1 : 1;
                });
            }

            foreach ( $data[ 'current' ][ 'parents' ] as $parentManagement => $row )
            {
                foreach ( $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'statuses' ] as $status => $item )
                {
                    $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'statuses' ][ $status ][ 1 ] = $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'total' ] ? number_format( $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'statuses' ][ $status ][ 0 ] / $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'total' ] * 100, 2 ) : 0;
                }
                $avg_cnt = count( $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'avg_rate' ] );
                if ( $avg_cnt )
                {
                    $avg_sum = array_sum( $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'avg_rate' ] );
                    $avg_rate = $avg_sum / $avg_cnt;
                    $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'avg_rate' ] = number_format( $avg_rate, 2 );
                    $rating = $avg_rate
                        * ( ( 100 - $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'statuses' ][ 'not_completed' ][ 1 ] ) / 100 )
                        * ( ( 100 - $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'statuses' ][ 'expired' ][ 1 ] ) / 100 )
                        * ( ( 100 - $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'statuses' ][ 'in_process' ][ 1 ] ) / 100 );
                    $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'rating' ] = number_format( $rating, 2 );
                }
                else
                {
                    $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'avg_rate' ] = number_format( 0, 2 );
                    $data[ 'current' ][ 'parents' ][ $parentManagement ][ 'rating' ] = number_format( 0, 2 );
                }
            }

            uasort( $data[ 'current' ][ 'parents' ], function ( $a, $b )
            {
                return $a[ 'rating' ] > $b[ 'rating' ] ? -1 : 1;
            });

            foreach ( $data[ 'current' ][ 'managements' ] as $management => $row )
            {
                $data[ 'current' ][ 'managements' ][ $management ][ 'completed_percent' ] = $data[ 'current' ][ 'managements' ][ $management ][ 'total' ] ? round( $data[ 'current' ][ 'managements' ][ $management ][ 'completed' ] / $data[ 'current' ][ 'managements' ][ $management ][ 'total' ] * 100 ) : 0;
                $data[ 'current' ][ 'managements' ][ $management ][ 'expired_percent' ] = $data[ 'current' ][ 'managements' ][ $management ][ 'total' ] ? round( $data[ 'current' ][ 'managements' ][ $management ][ 'expired' ] / $data[ 'current' ][ 'managements' ][ $management ][ 'total' ] * 100 ) : 0;
                $avg_cnt = count( $row[ 'avg_rate' ] );
                if ( $avg_cnt )
                {
                    $avg_sum = array_sum( $row[ 'avg_rate' ] );
                    $data[ 'current' ][ 'managements' ][ $management ][ 'avg_rate' ] = number_format( $avg_sum / $avg_cnt, 2 );
                }
                else
                {
                    $data[ 'current' ][ 'managements' ][ $management ][ 'avg_rate' ] = number_format( 0, 2 );
                }
            }

            uasort( $data[ 'current' ][ 'managements' ], function ( $a, $b )
            {
                return $a[ 'completed_percent' ] > $b[ 'completed_percent' ] ? -1 : 1;
            });

            foreach ( $data[ 'current' ][ 'types' ] as $type => $row )
            {
                $data[ 'current' ][ 'types' ][ $type ][ 1 ] = $data[ 'current' ][ 'tickets' ] ? round( $data[ 'current' ][ 'types' ][ $type ][ 0 ] / $data[ 'current' ][ 'tickets' ] * 100 ) : 0;
                if ( isset( $data[ 'prev' ][ 'types' ][ $type ] ) && $data[ 'prev' ][ 'tickets' ] )
                {
                    $data[ 'prev' ][ 'types' ][ $type ][ 1 ] = round( $data[ 'prev' ][ 'types' ][ $type ][ 0 ] / $data[ 'prev' ][ 'tickets' ] * 100 );
                }
                else
                {
                    $data[ 'prev' ][ 'types' ][ $type ] = [ 0, 0, 0 ];
                }
                $data[ 'current' ][ 'types' ][ $type ][ 2 ] = round( $data[ 'current' ][ 'types' ][ $type ][ 1 ] - $data[ 'prev' ][ 'types' ][ $type ][ 1 ] );
            }

            uasort( $data[ 'current' ][ 'types' ], function ( $a, $b )
            {
                return $a[ 0 ] > $b[ 0 ] ? -1 : 1;
            });

            $this->report->setData( $data );
            $this->report->save();

        }
        catch ( \Exception $e )
        {
            $logs->addCritical( 'Exception', [ $e ] );
        }

    }

}
