<?php

namespace App\Http\Controllers\Admin;

use App\Models\ManagementSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class SubscriptionsController extends BaseController
{

    public function index ( Request $request )
    {

        $subscriptions = ManagementSubscription
            ::mine()
            ->select(
                'managements_subscriptions.*',
                'managements.name AS management_name'
            )
            ->join( 'managements', 'managements.id', '=', 'managements_subscriptions.management_id' )
            ->orderBy( 'managements.name' )
            ->orderBy( 'managements_subscriptions.last_name' )
            ->orderBy( 'managements_subscriptions.first_name' );

        if ( ! empty( $request->get( 'management_name' ) ) )
        {
            $subscriptions
                ->whereLike( 'managements.name', $request->get( 'management_name' ) );
        }

        if ( ! empty( $request->get( 'last_name' ) ) )
        {
            $subscriptions
                ->whereLike( 'managements_subscriptions.last_name', $request->get( 'last_name' ) );
        }

        if ( ! empty( $request->get( 'first_name' ) ) )
        {
            $subscriptions
                ->whereLike( 'managements_subscriptions.first_name', $request->get( 'first_name' ) );
        }

        if ( ! empty( $request->get( 'telegram_id' ) ) )
        {
            $subscriptions
                ->whereLike( 'managements_subscriptions.telegram_id', $request->get( 'telegram_id' ) );
        }

        if ( ! empty( $request->get( 'username' ) ) )
        {
            $subscriptions
                ->whereLike( 'managements_subscriptions.username', $request->get( 'username' ) );
        }

        if ( ! empty( $request->get( 'created_at' ) ) )
        {
            $created_at = Carbon::parse( $request->get( 'created_at' ) )->toDateString();
            $subscriptions
                ->whereRaw( 'DATE( managements_subscriptions.created_at ) = ?', [ $created_at ] );
        }

        $subscriptions = $subscriptions
            ->paginate( config( 'pagination.per_page' ) );

        return view( 'admin.subscriptions.index' )
            ->with( 'subscriptions', $subscriptions );

    }

    public function destroy( $id )
    {
        $subscription = ManagementSubscription::find( $id );
        if ( ! $subscription )
        {
            return redirect()
                ->route( 'subscriptions.index' )
                ->withErrors( [ 'Подписка не найдена' ] );
        }
        $log = $subscription->addLog( 'Подписка прекращена' );
        if ( $log instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $log );
        }
        $subscription->sendTelegram( 'Ваша подписка на <b>' . $subscription->management->name . '</b> прекращена' );
        $subscription->delete();
        return redirect()->route( 'subscriptions.index' )
            ->with( 'success', 'Подписка успешно завершена' );
    }

    public function show ( $id )
    {
        return redirect()->route( 'subscriptions.index' );
    }

    public function edit ( $id )
    {
        return redirect()->route( 'subscriptions.index' );
    }

    public function create ()
    {
        return redirect()->route( 'subscriptions.index' );
    }

    public function update ( Request $request, $id )
    {
        return redirect()->route( 'subscriptions.index' );
    }

    public function store ( Request $request )
    {
        return redirect()->route( 'subscriptions.index' );
    }

}