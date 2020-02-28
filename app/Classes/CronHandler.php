<?php

namespace App\Classes;


use App\Models\Ticket;
use App\Models\Type;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CronHandler
{

    public function handleFavoriteTypes ()
    {

        $types = Type::all();

        foreach ( $types as $type )
        {
            $ticketsCount = Ticket::where( 'type_id', $type->id )
                ->count();
            $type->tickets_using_times = $ticketsCount;
            $type->save();
        }

        $users = User::all();

        foreach ( $users as $user )
        {
            $userTypesArray = [];

            $usersTickets = Ticket::where( 'author_id', $user->id )
                ->whereNotNull( 'type_id' )
                ->pluck( 'type_id' );
            if ( count( $usersTickets ) )
            {

                foreach ( $usersTickets as $usersTicket )
                {
                    if ( isset( $userTypesArray[ $usersTicket ] ) )
                    {
                        $userTypesArray[ $usersTicket ] = ++ $userTypesArray[ $usersTicket ];
                    } else
                    {
                        $userTypesArray[ $usersTicket ] = 1;
                    }
                }

                $userTypesJson = json_encode( array_keys( array_reverse( array_sort( $userTypesArray ), true ) ) );

                $user->favorite_types_list = $userTypesJson;

                $user->save();
            }

            Cache::remember( 'types_list_for_ticket_edit' . $user->id, 60 * 60 * 24 * 7, function ()
            {
                $res = Type
                    ::mine()
                    ->where( function ( $q )
                    {
                        return $q
                            ->whereNotNull( 'parent_id' );
                    } )
                    ->orderBy( 'name' )
                    ->get();
                $types = [];
                foreach ( $res as $r )
                {
                    $types[ $r->parent->name ?? 'Текущий' ][ $r->id ] = $r->name;
                }

                return ( new Type() )->sortByUsersFavoriteTypes( $types );
            } );
        }
    }

}
