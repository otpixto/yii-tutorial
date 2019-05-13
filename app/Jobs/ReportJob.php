<?php

namespace App\Jobs;

use App\Models\Asterisk\Cdr;
use App\Models\Report;
use App\Models\TicketManagement;
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

            $diff_days = $date_from->diffInDays( $date_to );
            $date_prev_from = ( clone $date_from )->subDays( $diff_days )->subSecond();

            $ticketManagements = TicketManagement
                ::mine()
                ->whereBetween( 'created_at', [ $date_prev_from, $date_to ] )
                ->whereNotIn( 'status_code', [ 'draft', 'moderate', 'created', 'no_contract', 'rejected_operator' ] )
                ->get();

            $data = [
                'calls' => 0,
                'tickets' => 0,
                'current'=> [
                    'statuses' => [
                        'total'             => [
                            'uk'    => [ 0, 100, 0 ],
                            'rso'   => [ 0, 100, 0 ],
                        ],
                        'completed'         => [
                            'uk'    => [ 0, 0, 0 ],
                            'rso'   => [ 0, 0, 0 ],
                        ],
                        'in_process'        => [
                            'uk'    => [ 0, 0, 0 ],
                            'rso'   => [ 0, 0, 0 ],
                        ],
                        'cancel'            => [
                            'uk'    => [ 0, 0, 0 ],
                            'rso'   => [ 0, 0, 0 ],
                        ],
                        'waiting'           => [
                            'uk'    => [ 0, 0, 0 ],
                            'rso'   => [ 0, 0, 0 ],
                        ],
                        'expired'           => [
                            'uk'    => [ 0, 0, 0 ],
                            'rso'   => [ 0, 0, 0 ],
                        ],
                    ],
                    'types' => [],
                    'parents' => [],
                    'managements' => [],
                ],
                'prev'=> [
                    'statuses' => [
                        'total'             => [
                            'uk'    => [ 0 ],
                            'rso'   => [ 0 ],
                        ],
                        'completed'         => [
                            'uk'    => [ 0 ],
                            'rso'   => [ 0 ],
                        ],
                        'in_process'        => [
                            'uk'    => [ 0 ],
                            'rso'   => [ 0 ],
                        ],
                        'cancel'            => [
                            'uk'    => [ 0 ],
                            'rso'   => [ 0 ],
                        ],
                        'waiting'           => [
                            'uk'    => [ 0 ],
                            'rso'   => [ 0 ],
                        ],
                        'expired'           => [
                            'uk'    => [ 0 ],
                            'rso'   => [ 0 ],
                        ],
                    ],
                    'types' => [],
                    'parents' => [],
                    'managements' => [],
                ],
            ];

            foreach ( $ticketManagements as $ticketManagement )
            {

                if ( $ticketManagement->created_at->timestamp >= $date_from->timestamp )
                {
                    $data[ 'tickets' ] ++;
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

                $type = $ticketManagement->ticket->type->parent->name ?? $ticketManagement->ticket->type->name;
                if ( ! isset( $data[ $key ][ 'types' ][ $type ] ) )
                {
                    $data[ $key ][ 'types' ][ $type ][ 0 ] = 0;
                }
                $data[ $key ][ 'types' ][ $type ][ 0 ] ++;
                $parentManagement = $ticketManagement->management->parent->name ?? $ticketManagement->management->name;
                if ( ! isset( $data[ $key ][ 'parents' ][ $parentManagement ] ) )
                {
                    $data[ $key ][ 'parents' ][ $parentManagement ] = [
                        'total' => 0,
                        'completed' => 0,
                        'expired' => 0,
                        'in_process' => 0,
                        'not_completed' => 0,

                    ];
                }
                $data[ $key ][ 'parents' ][ $parentManagement ][ 'total' ] ++;

                $data[ $key ][ 'statuses' ][ 'total' ][ $key2 ][ 0 ] ++;

                if ( $ticketManagement->ticket->overdueDeadlineAcceptance() )
                {
                    $data[ $key ][ 'statuses' ][ 'expired' ][ $key2 ][ 0 ] ++;
                }
                else
                {
                    switch ( $ticketManagement->status_code )
                    {
                        case 'closed_with_confirm':
                        case 'closed_without_confirm':
                        case 'confirmation_operator':
                        case 'confirmation_client':
                        case 'not_verified':
                        case 'completed_with_act':
                        case 'completed_without_act':
                            $data[ $key ][ 'statuses' ][ 'completed' ][ $key2 ][ 0 ] ++;
                            break;
                        case 'transferred':
                        case 'transferred_again':
                        case 'accepted':
                        case 'assigned':
                        case 'in_process':
                            $data[ $key ][ 'statuses' ][ 'in_process' ][ $key2 ][ 0 ] ++;
                            break;
                        case 'rejected':
                        case 'cancel':
                            $data[ $key ][ 'statuses' ][ 'cancel' ][ $key2 ][ 0 ] ++;
                            break;
                        case 'waiting':
                            $data[ $key ][ 'statuses' ][ 'waiting' ][ $key2 ][ 0 ] ++;
                            break;
                    }
                }

            }

            $data[ 'calls' ] = Cdr
                ::whereIn( \DB::raw( 'RIGHT( dst, 10 )' ), [ '8005503115', '4995503115' ] )
                ->whereBetween( 'calldate', [ $date_from, $date_to ] )
                ->count();

            foreach ( $data[ 'current' ][ 'statuses' ] as $status => & $row )
            {
                if ( $status != 'total' )
                {
                    $row[ 'uk' ][ 1 ] = $data[ 'current' ][ 'statuses' ][ 'total' ][ 'uk' ][ 0 ] ? round( $row[ 'uk' ][ 0 ] / $data[ 'current' ][ 'statuses' ][ 'total' ][ 'uk' ][ 0 ] * 100, 1, PHP_ROUND_HALF_DOWN ) : 0;
                    $row[ 'rso' ][ 1 ] = $data[ 'current' ][ 'statuses' ][ 'total' ][ 'rso' ][ 0 ] ? round( $row[ 'rso' ][ 0 ] / $data[ 'current' ][ 'statuses' ][ 'total' ][ 'rso' ][ 0 ] * 100, 1, PHP_ROUND_HALF_DOWN ) : 0;
                }
                $row[ 'uk' ][ 2 ] = $row[ 'uk' ][ 0 ] ? round( 100 - $data[ 'prev' ][ 'statuses' ][ $status ][ 'uk' ][ 0 ] / $row[ 'uk' ][ 0 ] * 100 ) : 0;
                $row[ 'rso' ][ 2 ] = $row[ 'rso' ][ 0 ] ? round( 100 - $data[ 'prev' ][ 'statuses' ][ $status ][ 'rso' ][ 0 ] / $row[ 'rso' ][ 0 ] * 100 ) : 0;
            }

            foreach ( $data[ 'current' ][ 'types' ] as $type => & $row )
            {
                $row[ 1 ] = $data[ 'tickets' ] ? round( $row[ 0 ] / $data[ 'tickets' ] * 100, 1, PHP_ROUND_HALF_DOWN ) : 0;
                $row[ 2 ] = isset( $data[ 'prev' ][ 'types' ][ $type ] ) && $row[ 0 ] ? round( 100 - $data[ 'prev' ][ 'types' ][ $type ][ 0 ] / $row[ 0 ] * 100 ) : 0;
            }

            $this->report->setData( $data );
            $this->report->save();

        }
        catch ( \Exception $e )
        {
            $logs->addCritical( 'Exception', [ $e ] );
        }

    }

}
