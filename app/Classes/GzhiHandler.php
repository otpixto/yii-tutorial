<?php

namespace App\Classes;

use App\Jobs\GzhiJob;
use App\Models\GzhiApiProvider;
use App\Models\GzhiRequest;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\Log;
use Webpatser\Uuid\Uuid;

class GzhiHandler
{

    private $url;

    private $soapAction;

    private $soapGetStateAction;

    private $requestStatus;

    private $errorMessage;


    public function __construct ( $status = null )
    {
        $this->url = GzhiApiProvider::GJI_SOAP_URL;

        $this->soapAction = GzhiRequest::GZHI_REQUEST_IMPORT_METHOD;

        $this->soapGetStateAction = GzhiRequest::GZHI_REQUEST_GET_STATE_METHOD;

        $this->requestStatus = $status ?? GzhiRequest::GZHI_REQUEST_STATUS_REGISTERED;

        $this->errorMessage = '';
    }

    public function sendGzhiInfo ()
    {

        $ticketsCount = 0;

        $gzhiProviders = GzhiApiProvider::get();

        foreach ( $gzhiProviders as $gzhiProvider )
        {

            $providerName = $gzhiProvider->name;

            $tickets = Ticket::whereHas( 'building', function ( $building ) use ( $providerName )
            {
                return $building
                    ->where( 'lon', '!=', - 1 )
                    ->where( 'lat', '!=', - 1 )
                    ->whereRaw( "`name` like '%$providerName%'" );
            } )
                ->notFinaleStatuses()
                ->where( 'created_at', '>=', Carbon::now()
                    ->subMonth()
                    ->toDateTimeString() )
                ->with(
                    'building',
                    'managements',
                    'managements.management',
                    'type'
                )
                ->get();

            if ( ! count( $tickets ) ) continue;

            foreach ( $tickets as $ticket )
            {
                $oneTicketCount = $this->handleGzhiTicket( $ticket, $gzhiProvider );
                $ticketsCount += $oneTicketCount;
            }
        }

        $logText = "Заявок обработано: $ticketsCount; \n $this->errorMessage \n";

        echo $logText;

        Log::info( $logText );

        $log = \App\Models\Log::create( [
            'text' => $logText
        ] );

        $log->save();

    }

    public function handleGzhiTicket ( Ticket $ticket, GzhiApiProvider $gzhiProvider) : int
    {

        $username = $gzhiProvider->login;

        $password = $gzhiProvider->password;

        $accessData = $username . ':' . $password;

        $orgGuid = $gzhiProvider->org_guid;

        $dateReg = date( 'Y-m-d' );

        $packDate = date( 'Y-m-d\TH:i:s' );

        $gzhiRequest = GzhiRequest::where( [
            'ticket_id' => $ticket->id,
            'Action' => $this->soapAction
        ] )
            ->first();

        $ticket->load('managements');

        if ( ! $gzhiRequest )
        {

            $gzhiRequest = new GzhiRequest();

        }
        if ( ! isset( $ticket->managements[ 0 ]->management->guid ) || !$ticket->type->gzhi_code_type || !$ticket->type->gzhi_code)
        {
            return 0;
        }

        $address = (isset($ticket->building->name)) ? substr($ticket->building->name, 0, 49) : 'Пусто';

        $managementGuid = $ticket->managements[ 0 ]->management->guid;

        $email = $ticket->managements[ 0 ]->management->email ?? 'test@test.ru';

        $text = ( $ticket->postponed_comment == '' ) ? 'Пусто' : $ticket->postponed_comment;

        $packGuid = Uuid::generate();

        $appealGuid = ( ! empty( $gzhiRequest->PackGUID ) ) ? $gzhiRequest->PackGUID : Uuid::generate();

        $transportGuid = ( ! empty( $gzhiRequest->TransportGUID ) ) ? $gzhiRequest->TransportGUID : Uuid::generate();

        $numberReg = Uuid::generate();

        $data = <<<SOAP
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:eds="http://ais-gzhi.ru/schema/integration/eds/" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
   <soapenv:Header/>
   <soapenv:Body>
      <eds:importAppealRequest Id="?" eds:version="1.0.0.4">
         <eds:Header>
            <!--You may enter the following 3 items in any order-->
            <eds:OrgGUID>$orgGuid</eds:OrgGUID>
            <eds:PackGUID>$packGuid</eds:PackGUID>
            <eds:PackDate>$packDate</eds:PackDate>
         </eds:Header>
         <eds:Appeal>
            <eds:AppealGUID>$appealGuid</eds:AppealGUID>
            <eds:TransportGUID>$transportGuid</eds:TransportGUID>
            <eds:AppealInformation>
               <eds:CreationDate>$packDate</eds:CreationDate>
               <eds:Status>{$this->requestStatus}</eds:Status>
               <eds:Initiator>
                  <eds:Name>{$ticket->firstname}</eds:Name>
                  <eds:Mail>$email</eds:Mail>
                  <eds:Phone>{$ticket->phone}</eds:Phone>
                  <eds:PostAddress>$address</eds:PostAddress>
               </eds:Initiator>
               <eds:TypeAppeal>{$ticket->type->gzhi_code_type}</eds:TypeAppeal>
               <eds:KindAppeal>{$ticket->type->gzhi_code}</eds:KindAppeal>
               <eds:Address>$address</eds:Address>
               <eds:Text>$text</eds:Text>
               <eds:IsSkipAnswer>0</eds:IsSkipAnswer>
               <eds:OrgGUID>$managementGuid</eds:OrgGUID>
               <eds:NumberReg>$numberReg</eds:NumberReg>
               <eds:DateReg>$dateReg</eds:DateReg>
               <eds:DatePlan>$packDate</eds:DatePlan>
            </eds:AppealInformation>
         </eds:Appeal>
      </eds:importAppealRequest>
   </soapenv:Body>
</soapenv:Envelope>
SOAP;

        try
        {
            $curl = $curl = $this->proceedCurl( $accessData, $data, $this->soapAction );

            $response = curl_exec( $curl );

            $status_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

            curl_close( $curl );

            if ( $status_code != 200 )
            {
                $this->errorMessage .= "CURL status: $status_code; ";
            }

            $responseObject = new \SimpleXMLElement( $response );

            if ( isset( $responseObject->message ) )
            {
                $this->errorMessage .= $responseObject->message;
            }

            if ( ! count( $responseObject ) )
            {
                $this->errorMessage .= " Empty response; ";
            }

        }
        catch ( \Exception $e )
        {
            $this->errorMessage .= $e->getMessage();
        }
        finally
        {
            $gzhiRequest->fill( [
                'ticket_id' => $ticket->id,
                'Action' => $this->soapAction,
                'OrgGUID' => $orgGuid,
                'PackGUID' => $packGuid,
                'TransportGUID' => $transportGuid,
                'PackDate' => $packDate,
                'Status' => ( $this->errorMessage == '' ) ? GzhiRequest::GZHI_REQUEST_STATUS_IN_WORK : GzhiRequest::GZHI_REQUEST_STATUS_ERROR,
                'Error' => $this->errorMessage,
                'gzhi_api_provider_id' => $gzhiProvider->id,
                'attempts_count' => ++$gzhiRequest->attempts_count
            ] );

            $gzhiRequest->save();
        }

        if( $this->errorMessage != '' && $gzhiRequest->attempts_count < GzhiRequest::GZHI_REQUEST_MAX_ATTEMPTS_COUNT )
        {
            Job::dispatch( new GzhiJob( $ticket, $gzhiProvider ) )->late(300);
        }

        return 1;

    }

    public function getGzhiRequestsStatus ()
    {

        try
        {
            $soapAction = $this->soapGetStateAction;

            $gzhiRequests = GzhiRequest::where( [
                'Status' => GzhiRequest::GZHI_REQUEST_STATUS_IN_WORK,
                'Action' => $this->soapAction
            ] )
                ->get();

            $packDate = date( 'Y-m-d\TH:i:s' );

            $i = 0;

            foreach ( $gzhiRequests as $gzhiRequest )
            {

                $packGuid = Uuid::generate();

                $gzhiApiProvider = $gzhiRequest->gzhiApiProvider;

                $username = $gzhiApiProvider->login;

                $password = $gzhiApiProvider->password;

                $accessData = $username . ':' . $password;

                $data = <<<SOAP
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:eds="http://ais-gzhi.ru/schema/integration/eds/" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
   <soapenv:Header/>
   <soapenv:Body>
      <eds:getStateDSRequest Id="?" eds:version="1.0.0.2">
         <eds:Header>
            <eds:OrgGUID>$gzhiRequest->OrgGUID</eds:OrgGUID>\n
            <eds:PackGUID>$packGuid</eds:PackGUID>
            <eds:PackDate>$packDate</eds:PackDate>
         </eds:Header>
         <eds:PackGUID>{$gzhiRequest->PackGUID}</eds:PackGUID>
      </eds:getStateDSRequest>
   </soapenv:Body>
</soapenv:Envelope>
SOAP;

                $curl = $curl = $this->proceedCurl( $accessData, $data, $soapAction );

                $response = curl_exec( $curl );

                $status_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

                curl_close( $curl );

                if ( $status_code != 200 )
                {
                    $this->errorMessage .= "CURL status: $status_code; ";
                    continue;
                }

                $response = preg_replace( "/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response );

                if ( $response )
                {
                    $xml = new \SimpleXMLElement( $response );

                    if ( isset( $xml->faultstring ) )
                    {
                        $this->errorMessage .= $xml->faultstring;
                    }

                    if ( ! isset( $xml->soapenvBody ) )
                    {
                        $this->errorMessage .= " SOAP structure error; ";
                        continue;
                    }

                    $body = $xml->soapenvBody;

                    $status = $body->edsgetStateDSResult->edsGetObjectStateResult->edsTransportInformation->edsTransportStatus ?? "";

                    $gzhiErrors = $body->edsgetStateDSResult->edsGetObjectStateResult->edsTransportInformation->edsERRORS ?? [];

                    if ( count( $gzhiErrors ) )
                    {
                        foreach ( $gzhiErrors as $gzhiError )
                        {
                            $this->errorMessage .= "Ошибка {$gzhiRequest->PackGUID} | {$gzhiError->edsErrorCode} | {$gzhiError->edsErrorText} <br>";
                        }
                    } else
                    {
                        if ( isset( $gzhiErrors->edsErrorCode ) && isset( $gzhiErrors->edsErrorText ) )
                        {
                            $this->errorMessage .= "Ошибка {$gzhiRequest->PackGUID} | {$gzhiErrors->edsErrorCode} | {$gzhiErrors->edsErrorText} <br>";
                        }
                    }

                    if ( $status == GzhiRequest::GZHI_REQUEST_TRANSPORT_STATUS_SUCCESS )
                    {
                        $gzhiRequest->Status = GzhiRequest::GZHI_REQUEST_STATUS_COMPLETE;

                        $gzhiRequest->CompleteDate = date( 'Y-m-d H:i:s' );

                        $i ++;
                    } else
                    {
                        $gzhiRequest->Status = GzhiRequest::GZHI_REQUEST_STATUS_ERROR;

                        $gzhiRequest->Error = $this->errorMessage;
                    }
                    $gzhiRequest->save();
                }
            }

            $logText = "Заявок обработано: $i; \n $this->errorMessage \n";

            echo $logText;

            Log::info( $logText );

            $log = \App\Models\Log::create( [
                'text' => $logText
            ] );

            $log->save();
        }
        catch ( \Exception $e )
        {
            Log::info( $e->getMessage() );
            $log = \App\Models\Log::create( [
                'text' => $e->getMessage()
            ] );
            $log->save();
        }

    }

    private function proceedCurl ( $accessData, $data, $soapAction )
    {
        try
        {
            $headers = [
                'Content-type: text/xml',
                'Cache-Control: no-cache',
                'SOAPAction: "' . $soapAction . '"',
            ];
            $curl = curl_init();
            curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 1 );
            curl_setopt( $curl, CURLOPT_URL, $this->url );
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $curl, CURLOPT_USERPWD, $accessData );
            curl_setopt( $curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
            curl_setopt( $curl, CURLOPT_TIMEOUT, 30 );
            curl_setopt( $curl, CURLOPT_POST, 1 );
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
            return $curl;
        }
        catch ( \Exception $e )
        {
            Log::info( $e->getMessage() );
            $log = \App\Models\Log::create( [
                'text' => $e->getMessage()
            ] );
            $log->save();
        }
    }

}