<?php

namespace App\Http\Controllers;

use App\Classes\Asterisk;
use App\Classes\Title;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class ProfileController extends Controller
{

    public function __construct ()
    {
        $this->middleware( 'auth' );
        Title::add( 'Профиль пользователя' );
    }

    public function loginas ( Request $request, $id )
    {
        if ( ! \Auth::user()->admin && ! \Auth::user()
                ->can( 'admin.loginas' ) )
        {
            return redirect()
                ->route( 'users.index' )
                ->withErrors( [ 'У вас недостаточно прав' ] );
        }
        $user = User::find( $id );
        if ( ! $user )
        {
            return redirect()
                ->route( 'users.index' )
                ->withErrors( [ 'Пользователь не найден' ] );
        }
        if ( ! $user->providers->count() )
        {
            return redirect()
                ->route( 'users.index' )
                ->withErrors( [ 'У пользователя нет привязанных провайдеров' ] );
        }
        $provider = $user->providers->first();
        $redirect = ( $provider->ssl ? 'https://' : 'http://' ) . $provider->domain;
        \Auth::login( $user );
        return redirect()->to( $redirect );
    }

    public function pickupCall ( Request $request )
    {

        $channel = $request->get( 'channel' );

        if ( ! $channel )
        {
            return 'ERROR: Некорректные данные';
        }

        $session = \Auth::user()->openPhoneSession;

        if ( ! $session )
        {
            return 'ERROR: Телефон не авторизован';
        }

        $asterisk = new Asterisk( $session->provider->getAsteriskConfig() );
        $queue = $asterisk->queue();

        if ( ! $queue )
        {
            return 'ERROR: Очередь не найдена';
        }

        $number = $session->number;

        if ( ! isset( $queue[ 'list' ][ $number ] ) )
        {
            return 'ERROR: Телефон не найден в очереди';
        }

        if ( ! $queue[ 'list' ][ $number ][ 'isFree' ] )
        {
            return 'ERROR: Занято';
        }

        if ( $asterisk->redirect( $channel, $number, 'incoming' ) )
        {
            return 'SUCCESS: Переадресация прошла успешно';
        }
        else
        {
            return 'ERROR: Не получилось переадресовать звонок';
        }

    }

    public function getPhone ()
    {
        if ( ! \Auth::user()->openPhoneSession )
        {
            return redirect()->route( 'profile.phone_reg' );
        }
        Title::add( 'Информация об авторизациях' );
        return view( 'profile.phone' );
    }

    public function getPhoneReg ()
    {
        if ( \Auth::user()->openPhoneSession )
        {
            return redirect()->route( 'profile.phone' );
        }
        Title::add( 'Регистрация телефона' );
        return view( 'profile.phone_reg' );
    }

    public function postPhoneReg ( Request $request )
    {
        if ( \Auth::user()->openPhoneSession )
        {
            return redirect()->route( 'profile.phone' );
        }
        $this->validate( $request, [
            'number' => 'required|min:2|max:10',
        ]);
        $phoneSession = \Auth::user()->phoneSessionReg( $request->get( 'number' ) );
        if ( $phoneSession instanceof MessageBag )
        {
            return redirect()
                ->back()
                ->withErrors( $phoneSession );
        }
        return redirect()
            ->route( 'profile.phone' )
            ->with( 'success', 'Телефон успешно авторизован' );
    }

    public function postPhoneUnreg ()
    {
        \DB::beginTransaction();
        $log = \Auth::user()->openPhoneSession->addLog( 'Телефонная сессия завершена' );
        if ( $log instanceof MessageBag )
        {
            return redirect()
                ->back()
                ->withErrors( $log );
        }
        $res = \Auth::user()->phoneSessionUnreg();
        if ( $res instanceof MessageBag )
        {
            return redirect()
                ->back()
                ->withErrors( $res );
        }
        \DB::commit();
        return redirect()
            ->route( 'profile.phone_reg' )
            ->with( 'success', 'Телефон успешно разлогинен' );
    }

    public function info ( Request $request, $id )
    {
        if ( ! \Auth::user()->admin && ! \Auth::user()
                ->can( 'userinfo' ) )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Доступ запрещен' );
        }
        $user = User::find( $id );
        if ( ! $user )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Пользователь не найден' );
        }
        return view( 'modals.userinfo' )
            ->with( 'user', $user );
    }

}