<?php

namespace App\Http\Controllers;

use App\Classes\Asterisk;
use App\Models\PhoneSession;
use App\Models\Provider;
use App\Models\ProviderPhone;
use App\Models\Ticket;
use App\Models\TicketCall;
use App\Models\UserPhoneAuth;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Webpatser\Uuid\Uuid;
use SoapClient;

class RestController extends Controller
{

    private $is_auth = false;

    private $logs;

    private $errors = [
        100 => 'Авторизация провалена',
        101 => 'Авторизованный телефон не найден',
        102 => 'Пользователь отключен',
        103 => 'Для данного пользователя уже создан черновик',
        104 => 'Запись о звонке не найдена в БД',
        105 => 'Заявитель не найден',
        106 => 'Астериск ответил ошибкой',
        900 => 'Внутренняя ошибка',
    ];

    public function __construct ( Request $request )
    {
        \Debugbar::disable();
        $this->logs = new Logger( 'REST' );
        $this->logs->pushHandler( new StreamHandler( storage_path( 'logs/rest.log' ) ) );
        $this->logs->addInfo( 'Запрос от ' . $request->ip(), $request->all() );
    }

    public function index ()
    {

    }

    public function phoneAuth ( Request $request )
    {
        $code = $request->get( 'code' );
        $auth = UserPhoneAuth
            ::where( 'code', '=', $code )
            ->first();
        if ( ! $auth || ( $auth->user && $auth->user->openPhoneSession ) )
        {
            return $this->error( 100 );
        }
        \DB::beginTransaction();
        $number = $auth->number;
        $user_id = $auth->user_id;
        $provider_id = $auth->provider_id;
        $auth->delete();
        $asterisk = new Asterisk();
        $phoneSession = PhoneSession::create( [
            'provider_id' => $provider_id,
            'user_id' => $user_id,
            'number' => $number
        ] );
        if ( $phoneSession instanceof MessageBag )
        {
            return $this->error( 900 );
        }
        $phoneSession->save();
        $log = $phoneSession->addLog( 'Телефонная сессия началась' );
        if ( $log instanceof MessageBag )
        {
            return $this->error( 900 );
        }
        if ( ! $asterisk->queueAdd( $number ) )
        {
            return $this->error( 106 );
        }
        $phoneSession->user->number = $phoneSession->number;
        $phoneSession->user->save();
        \DB::commit();
        return $this->success();
    }

    public function customer ( Request $request )
    {

        if ( ! $this->auth( $request ) )
        {
            return $this->error( 100 );
        }

        $response = [
            'customer' => null,
            'provider' => null,
            'users' => []
        ];

        $phone_office = mb_substr( $request->get( 'phone_office' ), - 10 );

        $providerPhone = ProviderPhone
            ::where( 'phone', '=', $phone_office )
            ->first();

        if ( $providerPhone )
        {
            $response[ 'provider' ] = $providerPhone->name;
            if ( $providerPhone->provider )
            {
                $call_phone = mb_substr( $request->get( 'call_phone' ), - 10 );
                $customer = $providerPhone->provider->customers()
                    ->where( 'phone', '=', $call_phone )
                    ->orWhere( 'phone2', '=', $call_phone )
                    ->orderBy( 'id', 'desc' )
                    ->first();
                if ( $customer )
                {
                    $response[ 'customer' ] = [
                        'building' => $customer->getActualAddress(),
                        'name' => $customer->getName(),
                    ];
                }
                $response[ 'users' ] = $providerPhone->provider->phoneSessions()
                    ->pluck( PhoneSession::$_table . '.user_id' )
                    ->toArray();
            }
        }

        return $this->success( $response );

    }

    public function createOrUpdateCallDraft ( Request $request )
    {

        if ( ! $this->auth( $request ) )
        {
            return $this->error( 100 );
        }

        $session = PhoneSession
            ::notClosed()
            ->where( 'number', '=', $request->get( 'number' ) )
            ->first();
        if ( ! $session )
        {
            return $this->error( 101 );
        }
        if ( ! $session->user || ! $session->user->isActive() )
        {
            return $this->error( 102 );
        }

        $user = $session->user;

        $response = [
            'ticket' => null,
            'provider' => null,
            'user' => $user->id
        ];

        $draft = Ticket
            ::draft( $user->id )
            ->first();

        $phone = mb_substr( preg_replace( '/\D/', '', $request->get( 'phone' ) ), - 10 );
        $phone_office = mb_substr( preg_replace( '/\D/', '', $request->get( 'phone_office' ) ), - 10 );

        $provider = Provider
            ::mine( $user )
            ->whereHas( 'phones', function ( $q ) use ( $phone_office )
            {
                return $q
                    ->where( 'phone', '=', $phone_office );
            } )
            ->first();

        if ( ! $draft )
        {
            $draft = new Ticket();
            $draft->status_code = 'draft';
            $draft->status_name = Ticket::$statuses[ 'draft' ];
            $draft->author_id = $user->id;
            $draft->phone = $phone;
            $draft->call_phone = $draft->phone;
            $draft->call_id = $request->get( 'call_id' );
        } else
        {
            $draft->phone = $phone;
            $draft->call_phone = $draft->phone;
            $draft->call_id = $request->get( 'call_id' );
        }

        if ( $provider )
        {
            $draft->provider_id = $provider->id;
            $response[ 'provider' ] = $provider->name;
        }

        $draft->save();

        $response[ 'ticket' ] = $draft->id;

        return $this->success( $response );

    }

    public function user ( Request $request )
    {

        if ( ! $this->auth( $request ) )
        {
            return $this->error( 100 );
        }

        $session = PhoneSession
            ::notClosed()
            ->where( 'number', '=', $request->get( 'number' ) )
            ->first();
        if ( ! $session )
        {
            return $this->error( 101 );
        }
        if ( ! $session->user || ! $session->user->isActive() )
        {
            return $this->error( 102 );
        }

        $user = $session->user;

        $response = [
            'user' => $user->id
        ];

        return $this->success( $response );

    }

    public function ticketCall ( Request $request )
    {

        $ticketCall = TicketCall::find( $request->get( 'ticket_call_id' ) );
        if ( $ticketCall )
        {
            $ticketCall->call_id = $request->get( 'uniqueid' );
            $ticketCall->save();
        }

    }

    private function error ( $code = null )
    {
        $message = $this->errors[ $code ] ?? null;
        $this->logs->addError( 'Ошибка', [ $code, $message ] );
        return [
            'success' => false,
            'code' => $code,
            'message' => $message
        ];
    }

    private function success ( $message = null )
    {
        $this->logs->addInfo( 'Успешно', is_array( $message ) ? $message : [ $message ] );
        return [
            'success' => true,
            'message' => $message
        ];
    }

    private function auth ( Request $request )
    {
        if ( $this->is_auth ) return true;
        $this->logs->addInfo( 'Авторизация', $request->all() );
        $hash = $request->get( 'hash', null );
        if ( ! $hash ) return false;
        $data = $request->all();
        unset( $data[ 'hash' ] );
        ksort( $data );
        $arr = [];
        foreach ( $data as $key => $val )
        {
            $arr[] = $key . '=' . $val;
        }
        $arr[] = \Config::get( 'rest.password' );
        $hash = mb_strtolower( $hash );
        $_hash = mb_strtolower( md5( implode( '|', $arr ) ) );
        $status = $hash == $_hash;
        $this->is_auth = $status;
        return $status;
    }

    public function sendInfo ()
    {
        #$url = 'https://test-gzhi.eiasmo.ru/eds-service';
        $url = 'https://next-lk.eiasmo.ru/eds-service/';
        #$wsdl = 'https://test-gzhi.eiasmo.ru/eds-service/eds.wsdl';
        $wsdl = 'https://mo.eds-juk.ru/ais/eds.wsdl';
        #$orgGuid = 'A04C784E-EAF5-11E7-8A70-99D7D5FACC35';
        $orgGuid = 'A04C784E-EAF5-11E7-8A70-99D7D5FACC35';
        $username = 'es_eds_ram';
        //$username = 'user_omsu892661153630';
        $password = 'sYU2mMUl9H';
        $password = 's9d34f95df4e';

        $typeGuid = '19A16311-EBF0-4E27-8F64-D470868A5457';
        $addressGuid = 'A6BD8EF6-BA54-11E7-8E30-FE5F11EEAB0E';
        $ukGuid = '355F5138-BB06-11E7-9583-B5CD11EEAB0E';

        $date = '2019-07-08';
        $address = 'sa';
        $text = 'dsfwse';

        $uuid = Uuid::generate();
        $appealGuid = Uuid::generate();
        $transportGuid = Uuid::generate();
        $numberReg = Uuid::generate();
        $dateReg = date( 'Y-m-d' );

        $data = <<<SOAP
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:eds="http://ais-gzhi.ru/schema/integration/eds/" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
   <soapenv:Header/>
   <soapenv:Body>
      <eds:importAppealRequest Id="?" eds:version="1.0.0.2">
         <eds:Header>
            <!--You may enter the following 3 items in any order-->
            <eds:OrgGUID>$orgGuid</eds:OrgGUID>
            <eds:PackGUID>$uuid</eds:PackGUID>
            <eds:PackDate>2019-07-16T09:12:12</eds:PackDate>
         </eds:Header>
         <eds:Appeal>
            <eds:AppealGUID>$appealGuid</eds:AppealGUID>
            <eds:TransportGUID>$transportGuid</eds:TransportGUID>
            <eds:AppealInformation>
               <eds:CreationDate>2019-07-16T09:24:12</eds:CreationDate>
               <eds:Status>30</eds:Status>
               <eds:Initiator>
                  <eds:Name>Иванов Иван Иванович</eds:Name>
                  <eds:Mail>ivanov@mail.ru</eds:Mail>
                  <eds:Phone>+7-917-657-32-45</eds:Phone>
                  <eds:PostAddress>$address</eds:PostAddress>
               </eds:Initiator>
               <eds:TypeAppeal>1</eds:TypeAppeal>
               <eds:KindAppeal>1001</eds:KindAppeal>
               <eds:AddressGUID>$addressGuid</eds:AddressGUID>
               <eds:AddressNote>Около второго подъезда</eds:AddressNote>
               <eds:FlatNum>2</eds:FlatNum>
               <eds:Domofon>домофон 2</eds:Domofon>
               <eds:NeedAccess>Постучите 3 раза</eds:NeedAccess>
               <eds:Text>С крыши лед падает!!!</eds:Text>
               <eds:IsSkipAnswer>0</eds:IsSkipAnswer>
               <eds:OrgGUID>$ukGuid</eds:OrgGUID>
               <eds:NumberReg>$numberReg</eds:NumberReg>
               <eds:DateReg>$dateReg</eds:DateReg>
               <eds:DatePlan>2019-07-16T09:30:12</eds:DatePlan>
               <eds:Description>Какое-то описание</eds:Description>
            </eds:AppealInformation>
         </eds:Appeal>
      </eds:importAppealRequest>
   </soapenv:Body>
</soapenv:Envelope>
SOAP;

        $headers = [
            'Content-type: text/xml',
            'Cache-Control: no-cache',
            //'Content-length: ' . mb_strlen( $xml ),
            'SOAPAction: "ImportAppealData"',
        ];

        try
        {

            $curl = curl_init();
            curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 1 );
            curl_setopt( $curl, CURLOPT_URL, $url );
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $curl, CURLOPT_USERPWD, $username . ':' . $password ); // username and password - declared at the top of the doc
            curl_setopt( $curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
            curl_setopt( $curl, CURLOPT_TIMEOUT, 30 );
            curl_setopt( $curl, CURLOPT_POST, 1 );
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );

            $response = curl_exec( $curl );

            dd($response);

        }
        catch ( \Exception $e )
        {
            dd( $e );
        }
    }

}
