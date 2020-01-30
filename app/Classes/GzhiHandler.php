<?php

namespace App\Classes;

use App\Models\Building;
use App\Models\Customer;
use App\Models\File;
use App\Models\GzhiApiProvider;
use App\Models\GzhiRequest;
use App\Models\Management;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketManagement;
use App\Models\Type;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Webpatser\Uuid\Uuid;

class GzhiHandler
{

    private $url;

    private $soapAction;

    private $soapGetStateAction;

    private $requestStatus;

    private $errorMessage;

    private $apiVersion;


    public function __construct ( $status = null )
    {
        $this->url = env( 'EIAS_INTEGRATION_URL' ) ?? GzhiRequest::GJI_SOAP_URL;

        $this->fileRestUrl = env( 'EIAS_INTEGRATION_FILE_REST_URL' ) ?? GzhiRequest::EIAS_INTEGRATION_FILE_REST_URL;

        $this->soapAction = GzhiRequest::GZHI_REQUEST_IMPORT_METHOD;

        $this->soapExportAction = GzhiRequest::GZHI_REQUEST_EXPORT_METHOD;

        $this->soapGetStateAction = GzhiRequest::GZHI_REQUEST_GET_STATE_METHOD;

        $this->requestStatus = $status ?? GzhiRequest::GZHI_REQUEST_STATUS_REGISTERED;

        $this->apiVersion = GzhiRequest::GZHI_REQUEST_API_VERSION;

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
                ->whereIn( 'status_code', GzhiRequest::GZHI_STATUSES_LIST )
                ->where( 'updated_at', '>=', Carbon::now()
                    ->subDay()
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

        $log = \App\Models\Log::create( [
            'text' => $logText
        ] );

        $log->save();

    }

    public function handleGzhiTicket ( Ticket $ticket, GzhiApiProvider $gzhiProvider ) : int
    {
        $ticket->load( 'managements' );

        $ticket->load( 'customer' );

        if ( ! isset( $ticket->customer ) || ! isset( $ticket->managements[ 0 ] ) )
        {
            return 0;
        }

        $ticket->load( 'vendors' );

        $ticket->load( 'status' );

        $username = $gzhiProvider->login;

        $password = $gzhiProvider->password;

        $accessData = $username . ':' . $password;

        $orgGuid = $gzhiProvider->org_guid;

        $changingDate = $ticket->updated_at ?? $ticket->created_at;

        $dateReg = Carbon::parse( $changingDate )
            ->format( 'Y-m-d' );

        $packDate = Carbon::parse( $changingDate )
            ->format( 'Y-m-d\TH:i:s' );

        $deadLine = $ticket->deadline_execution ?? $ticket->deadline_acceptance ?? $ticket->created_at;

        $planDate = Carbon::parse( $deadLine )
            ->format( 'Y-m-d' );

        $gzhiRequest = GzhiRequest::where( [
            'ticket_id' => $ticket->id,
            'Action' => $this->soapAction
        ] )
            ->first();

        if ( $gzhiRequest )
        {

            if ( $gzhiRequest->Status != GzhiRequest::GZHI_REQUEST_STATUS_ERROR && $gzhiRequest->ticket_status_code == $ticket->status_code )
            {
                return 0;
            }

        } else
        {
            $gzhiRequest = new GzhiRequest();
        }

        $ticket->customer->load( 'buildings' );

        $appealGuid = ( ! empty( $gzhiRequest->appeal_guid ) ) ? $gzhiRequest->appeal_guid : (string) Uuid::generate();

        $managementGuid = $ticket->managements[ 0 ]->management->parent->gzhi_guid ?? $ticket->managements[ 0 ]->management->parent->guid ?? $ticket->managements[ 0 ]->management->gzhi_guid ?? $ticket->managements[ 0 ]->management->guid;

        if ( ! isset( $ticket->managements[ 0 ]->management )
            || ! $ticket->type->gzhi_code_type
            || ! $ticket->type->gzhi_code
            || $ticket->building->gzhi_address_guid == null
            || $ticket->type_id == null
            || ! in_array( $ticket->status_code, GzhiRequest::GZHI_STATUSES_LIST )
            || $managementGuid == '355D5C52-BB06-11E7-9583-B5CD11EEAB0E'
            || ! $ticket->status->gzhi_status_code )
        {
            return 0;
        }

        $actualAddress = $ticket->customer->getActualAddress() ?? '';

        $address = ( $actualAddress != '' ) ? mb_substr( str_replace( 'Московская обл., ', '', $actualAddress ), 0, 49 ) : ( mb_substr( $ticket->building->name, 0, 49 ) );

        $gzhiAddressGUID = $ticket->building->gzhi_address_guid;

        $text = ( $ticket->text == '' ) ? 'Пусто' : $ticket->text;

        $packGuid = Uuid::generate();

        $transportGuid = ( ! empty( $gzhiRequest->TransportGUID ) ) ? $gzhiRequest->TransportGUID : Uuid::generate();

        $numberReg = Uuid::generate();

        $name = $ticket->getName() ?? $ticket->lastname . " " . $ticket->firstname . " " . $ticket->middlename;

        $prolongReason = GzhiRequest::GZHI_DEFAULT_PROLONG_REASON;

        if ( $ticket->status->gzhi_status_code > 50 )
        {
            $isDone = 'true';

            $answer = ( $ticket->postponed_comment == '' ) ? 'Выполнено' : $ticket->postponed_comment;

            $fact = "<eds:Fact>
                  <eds:DateFact>$packDate</eds:DateFact>
                  <eds:IsDone>$isDone</eds:IsDone>
                  <eds:Answer>$answer</eds:Answer>
		        </eds:Fact>";
        } else
        {
            $fact = '';
        }

        if ( $ticket->gzhi_appeal_number )
        {
            $appealNumber = "<eds:AppealNumber>{$ticket->gzhi_appeal_number}</eds:AppealNumber>";
        } else
        {
            $appealNumber = "";
        }

        $initiatorFiles = '';
//        foreach ( $ticket->comments as $comment )
//        {
//            foreach ( $comment->files()
//                          ->get() as $file )
//            {
//
//                $path = storage_path( 'app' ) . '/' . $file->path;
//
//                $fileHash = md5_file( $path );
//                $fileGUID = Uuid::generate();
//                $initiatorFiles .= "<eds:ProlongFile>
//                     <eds:Name>{$file->name}</eds:Name>
//                     <eds:FileName>{$file->name}</eds:FileName>
//                     <eds:AttachmentGUID>$appealGuid</eds:AttachmentGUID>
//                     <eds:AttachmentHASH>$fileHash</eds:AttachmentHASH>
//                  </eds:ProlongFile>
//               ";
//            }
//
//        }

        $executors = '';
        if ( $ticket->managements[ 0 ]->management->executors()
            ->first() )
        {
            $executors .= "<eds:WorkerFIO>{$ticket->managements[ 0 ]->management->executors()
            ->first()->name}</eds:WorkerFIO>";
        }

        $data = <<<SOAP
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:eds="http://ais-gzhi.ru/schema/integration/eds/" encoding="utf-8" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
   <soapenv:Header/>
   <soapenv:Body>
      <eds:importAppealRequest Id="?" eds:version="{$this->apiVersion}">
         <eds:Header>
            <eds:OrgGUID>$orgGuid</eds:OrgGUID>
            <eds:PackGUID>$packGuid</eds:PackGUID>
            <eds:PackDate>$packDate</eds:PackDate>
         </eds:Header>
         <eds:Appeal>
            <eds:AppealGUID>$appealGuid</eds:AppealGUID>
            $appealNumber
            <eds:TransportGUID>$transportGuid</eds:TransportGUID>
            <eds:AppealInformation>
               <eds:CreationDate>$packDate</eds:CreationDate>
               <eds:Status>{$ticket->status->gzhi_status_code}</eds:Status>
               <eds:Initiator>
                  <eds:Name>$name</eds:Name>
                  <eds:Phone>{$ticket->phone}</eds:Phone>
                  <eds:PostAddress>$address</eds:PostAddress>
               </eds:Initiator>
               <eds:TypeAppeal>{$ticket->type->gzhi_code_type}</eds:TypeAppeal>
               <eds:KindAppeal>{$ticket->type->gzhi_code}</eds:KindAppeal>
               <eds:AddressGUID>$gzhiAddressGUID</eds:AddressGUID>
               <eds:Text>$text</eds:Text>
               <eds:OrgGUID>$managementGuid</eds:OrgGUID>
               <eds:NumberReg>$numberReg</eds:NumberReg>
               <eds:DateReg>$dateReg</eds:DateReg>
               <eds:DatePlan>$planDate</eds:DatePlan>
               $executors
               <eds:Prolong>
                  <eds:ProlongReasons>$prolongReason</eds:ProlongReasons>
                  $initiatorFiles
               </eds:Prolong>
               $initiatorFiles
               $fact
            </eds:AppealInformation>
         </eds:Appeal>
      </eds:importAppealRequest>
   </soapenv:Body>
</soapenv:Envelope>
SOAP;

        $log = \App\Models\Log::create( [
            'text' => $data
        ] );

        $log->save();

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
                'attempts_count' => ++ $gzhiRequest->attempts_count,
                'appeal_guid' => $appealGuid,
                'ticket_status_code' => $ticket->status_code
            ] );

            $gzhiRequest->save();

        }

//        if ( $this->errorMessage != '' && $gzhiRequest->attempts_count < GzhiRequest::GZHI_REQUEST_MAX_ATTEMPTS_COUNT && method_exists($ticket, 'dispatch') )
//        {
//            $ticket->dispatch( new GzhiJob( $ticket, $gzhiProvider ) )
//                ->late( 300 );
//        }

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
                ->where( 'attempts_count', '<', GzhiRequest::GZHI_REQUEST_MAX_ATTEMPTS_COUNT )
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
      <eds:getStateDSRequest Id="?" eds:version="{$this->apiVersion}">
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

                    $objectGUID = $body->edsgetStateDSResult->edsGetObjectStateResult->edsObjectInformation->edsObjectGUID ?? null;

                    if ( $objectGUID )
                    {
                        $ticket = Ticket::find( $gzhiRequest->ticket_id );

                        if ( $ticket )
                        {
                            $gzhiDate = $body->edsgetStateDSResult->edsGetObjectStateResult->edsObjectInformation->edsObjectState->edsUpdateDate ?? date( 'Y-m-d H:i:s' );

                            $gzhiDate = Carbon::parse( (String) $gzhiDate )
                                ->format( 'Y-m-d H:i:s' );

                            $ticket->vendor_number = (String) $objectGUID;

                            $ticket->vendor_date = $gzhiDate;

                            $ticket->save();
                        }
                    }

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

                    } else
                    {
                        $gzhiRequest->Status = GzhiRequest::GZHI_REQUEST_STATUS_ERROR;

                        $gzhiRequest->Error = $this->errorMessage;
                    }
                    $gzhiRequest->save();

                }
                $i ++;
            }

            $logText = "Заявок обработано: $i; \n $this->errorMessage \n";

            echo $logText;

            $log = \App\Models\Log::create( [
                'text' => $logText
            ] );

            $log->save();
        }
        catch ( \Exception $e )
        {

            $log = \App\Models\Log::create( [
                'text' => $e->getMessage()
            ] );

            $log->save();

        }

    }

    public function fillTypes ()
    {

        $packGuid = Uuid::generate();

        $soapAction = $this->soapGetStateAction;

        $gzhiApiProvider = GzhiApiProvider::whereName( 'Жуковский' )
            ->first();

        $orgGuid = $gzhiApiProvider->org_guid;

        $username = $gzhiApiProvider->login;

        $password = $gzhiApiProvider->password;

        $accessData = $username . ':' . $password;

        $packDate = date( 'Y-m-d\TH:i:s' );

        $data = <<<SOAP
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:eds="http://ais-gzhi.ru/schema/integration/eds/" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
    <soapenv:Header/>
    <soapenv:Body>
        <eds:getStateDSRequest Id="?" eds:version="{$this->apiVersion}">
            <eds:Header>
                <eds:OrgGUID>$orgGuid</eds:OrgGUID>
                <eds:PackGUID>$packGuid</eds:PackGUID>
                <eds:PackDate>$packDate</eds:PackDate>
            </eds:Header>
            <eds:PackGUID>67b3ab59-b1fd-11e9-8c02-ddd67477fd67</eds:PackGUID>
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
            $log = \App\Models\Log::create( [
                'text' => $this->errorMessage
            ] );

            $log->save();
            return false;
        }

        $response = preg_replace( "/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response );

        $xml = new \SimpleXMLElement( $response );

        if ( isset( $xml->faultstring ) )
        {
            $this->errorMessage .= $xml->faultstring;
            $log = \App\Models\Log::create( [
                'text' => $this->errorMessage
            ] );

            $log->save();
        }

        if ( ! isset( $xml->soapenvBody ) )
        {
            $this->errorMessage .= " SOAP structure error; ";
            $log = \App\Models\Log::create( [
                'text' => $this->errorMessage
            ] );

            $log->save();
            return false;
        }

        $gzhiTypes = $xml->soapenvBody->edsgetStateDSResult->edsGetNsiResult->edsAppealKind;

        $i = 0;

        $dataArray = [];

        foreach ( $gzhiTypes as $gzhiType )
        {

            $dataArray[ $i ][ 'edsCode' ] = (string) $gzhiType->edsCode;
            $dataArray[ $i ][ 'edsCodeType' ] = (string) $gzhiType->edsCodeType;
            $dataArray[ $i ][ 'edsName' ] = (string) $gzhiType->edsName;

            $i ++;

        }

        $headersArray = array( 'edsCode', 'edsCodeType', 'edsName' );

        $this->generateCSV( $headersArray, $dataArray );

    }

    public function fillAddresses ()
    {
        ini_set('max_execution_time', 1000);

        set_time_limit(0);

        $soapAction = $this->soapGetStateAction;

        $gzhiApiProvider = GzhiApiProvider::whereName( 'Раменск' )
            ->first();

        $orgGuid = $gzhiApiProvider->org_guid;

        $username = $gzhiApiProvider->login;

        $password = $gzhiApiProvider->password;

        $accessData = $username . ':' . $password;

        $packDate = date( 'Y-m-d\TH:i:s' );

//        $packGuidArray = [];
//
//        for($j=295; $j<300; $j++)
//        {
//            $packGuid0 = Uuid::generate();
//
//            $data0 = <<<SOAP
//<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:eds="http://ais-gzhi.ru/schema/integration/eds/" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
//   <soapenv:Header/>
//   <soapenv:Body>
//      <eds:getNsiDSRequest Id="?" eds:version="1.0.0.7">
//         <!--Optional:-->
//        <eds:Header>
//            <!--You may enter the following 3 items in any order-->
//            <eds:OrgGUID>A04C784E-EAF5-11E7-8A70-99D7D5FACC35</eds:OrgGUID>
//            <eds:PackGUID>$packGuid0</eds:PackGUID>
//            <eds:PackDate>2020-01-14T09:12:12</eds:PackDate>
//         </eds:Header>
//         <eds:Addresses>
//            <eds:OrgManager>true</eds:OrgManager>
//            <eds:AllTypes>true</eds:AllTypes>
//         </eds:Addresses>
//         <eds:Page>$j</eds:Page>
//      </eds:getNsiDSRequest>
//   </soapenv:Body>
//</soapenv:Envelope>
//SOAP;
//
//            $curl = $curl = $this->proceedCurl( $accessData, $data0, 'GetNsiDS' );
//
//            $response = curl_exec( $curl );
//
//            $status_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
//
//            curl_close( $curl );
//
//            if ( $status_code != 200 )
//            {
//                $this->errorMessage .= "CURL status: $status_code; ";
//                $log = \App\Models\Log::create( [
//                    'text' => $this->errorMessage
//                ] );
//
//                $log->save();
//                return false;
//            }
//
//            $response = preg_replace( "/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response );
//
//            $xml = new \SimpleXMLElement( $response );
//
//            if ( isset( $xml->faultstring ) )
//            {
//                $this->errorMessage .= $xml->faultstring;
//                $log = \App\Models\Log::create( [
//                    'text' => $this->errorMessage
//                ] );
//
//                $log->save();
//            }
//
//            if ( ! isset( $xml->soapenvBody ) )
//            {
//                $this->errorMessage .= " SOAP structure error; ";
//                $log = \App\Models\Log::create( [
//                    'text' => $this->errorMessage
//                ] );
//
//                $log->save();
//                return false;
//            }
//
//            if(!isset($xml->soapenvBody->Success->PackGUID)){
//                continue;
//            }
//
//            $packG = (string)$xml->soapenvBody->Success->PackGUID;
//
//            echo '"' . $packG . '",';
//
//            $packGuidArray[] = [$packG];
//        }
//
//        $headersArray = array( 'edsAddressGUID'
//        );
//
//        $this->generateCSV( $headersArray, $packGuidArray, $j );
//
//        exit();


//        $packGuidArray = [
////            "1cb508b0-37a4-11ea-9030-89d23c0ba670","1d073580-37a4-11ea-ba7b-570600e9c439","1d5d7fd0-37a4-11ea-b119-35fe0d6f9dad","1daedfc0-37a4-11ea-ac3b-93ed081bc6dc","1e0410d0-37a4-11ea-a7ba-6577c09b15ae","1e5cf5a0-37a4-11ea-bbb3-9b811cbbd2c7","1eb5cce0-37a4-11ea-a28d-cf93cdb8df47","1f085bf0-37a4-11ea-aaaf-91243c896918","1f5b2aa0-37a4-11ea-8245-69610bbcbf00","1fb8b6e0-37a4-11ea-a3af-57b28be78189","200c7ae0-37a4-11ea-8a49-1d0862b5644f","20692a10-37a4-11ea-8075-7d36104281a4","20bce8f0-37a4-11ea-8562-8347867c3d15","21158280-37a4-11ea-b12f-4b57559fbb5c","21695340-37a4-11ea-a99d-37d55c8371b7","21bbbdc0-37a4-11ea-89bf-a1f3fa9de6fd","220f1d20-37a4-11ea-b80e-f795b32df0d9","225fd390-37a4-11ea-b391-2352723e7bcf","22b30950-37a4-11ea-9dba-4d928b5822bb","23083220-37a4-11ea-936a-37ab306f7b55","236663c0-37a4-11ea-a5d4-5b23c8ce81f9",
//
////            "23c29370-37a4-11ea-b801-351bfb658c58","241a8060-37a4-11ea-8f14-a3e228f0a098","246f9c10-37a4-11ea-81a1-ff77f27862ec",
//
////            "24c709a0-37a4-11ea-a2de-3de5227568bb","251e9850-37a4-11ea-b454-6d766473844d","2579e520-37a4-11ea-86fd-b79328cbeb2c","25d2fb20-37a4-11ea-ae32-0795aa163971","262371b0-37a4-11ea-8bda-6590d8048762","2673ccb0-37a4-11ea-bd02-458817e1e267",
//
////            "26c583c0-37a4-11ea-8a31-d96f7bc7d2b1","271b9160-37a4-11ea-8dc2-2fc2487e5bed","277217c0-37a4-11ea-943a-23fda30e5806","27c4c020-37a4-11ea-ad22-eb7ad0c4ea7b","281821c0-37a4-11ea-aac3-e36a2749e983","286796b0-37a4-11ea-958c-3b47c261f35b",
//
////            "28c72380-37a4-11ea-ad2b-0bfb339736a5","291ec020-37a4-11ea-b170-675d0b7a2950","2970f3c0-37a4-11ea-b623-af6ca4c5bd29","29ccddc0-37a4-11ea-94b7-3bf48853b9c8","2a1f4dc0-37a4-11ea-ae3e-87843c6df1ed","2a6fc5f0-37a4-11ea-9121-c9fd97f4f3f8",
////
//"2ac20ad0-37a4-11ea-931c-5df4a7098e6f","2b141e30-37a4-11ea-8593-bb1e234744fa","2b65a440-37a4-11ea-a726-e1d9a0777889","2bbd4ec0-37a4-11ea-8b0c-7347942ea76a","2c1195b0-37a4-11ea-a745-593f96718c48","2c600180-37a4-11ea-9583-9fa52fa49156",
//        ];

        $l = 135;

        $fileName = 'files/ram_addr_' . $l . '.csv';

        $csvData = \Illuminate\Support\Facades\File::get( storage_path( $fileName ) );

        $lines = explode( PHP_EOL, $csvData );

        $array = array();

        $i = 0;

        foreach ( $lines as $line )
        {
            if ( $i > 0 )
            {
                $array[] = str_getcsv( $line );
            }

            $i ++;
        }


        $dataArray = [];

        $i = 0;

        $gArray = [];

        foreach ($array as $addressPackGuid)
        {

            $addressPackGuid = $addressPackGuid[0];
            $packGuid = Uuid::generate();

        $data = <<<SOAP
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:eds="http://ais-gzhi.ru/schema/integration/eds/" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
    <soapenv:Header/>
    <soapenv:Body>
        <eds:getStateDSRequest Id="?" eds:version="{$this->apiVersion}">
            <eds:Header>
                <eds:OrgGUID>$orgGuid</eds:OrgGUID>
                <eds:PackGUID>$packGuid</eds:PackGUID>
                <eds:PackDate>$packDate</eds:PackDate>
            </eds:Header>
            <eds:PackGUID>$addressPackGuid</eds:PackGUID>
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
            $log = \App\Models\Log::create( [
                'text' => $this->errorMessage
            ] );

            $log->save();
            return false;
        }

        $response = preg_replace( "/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response );

        $xml = new \SimpleXMLElement( $response );

        if ( isset( $xml->faultstring ) )
        {
            $this->errorMessage .= $xml->faultstring;
            $log = \App\Models\Log::create( [
                'text' => $this->errorMessage
            ] );

            $log->save();
        }

        if ( ! isset( $xml->soapenvBody ) )
        {
            $this->errorMessage .= " SOAP structure error; ";
            $log = \App\Models\Log::create( [
                'text' => $this->errorMessage
            ] );

            $log->save();
            return false;
        }

        if(!isset($xml->soapenvBody->edsgetStateDSResult->edsGetNsiResult->edsAddresses)){
            continue;
        }

        $gzhiAddresses = $xml->soapenvBody->edsgetStateDSResult->edsGetNsiResult->edsAddresses;


//        foreach ( $gzhiAddresses as $gzhiAddress )
//        {
//
//            $building = Building::where( 'name', 'like', '%' . $gzhiAddress->edsAddressName . '%' )
//                ->first();
//
//            if ( $building )
//            {
//                $building->fais_address_guid = $gzhiAddress->edsFIASAddressGUID ?? '';
//
//                $building->gzhi_address_guid = $gzhiAddress->edsAddressGUID ?? '';
//
//                $building->save();
//
//                $i ++;
//            }
//        }
//
//        $buildings = Building::all();
//
//
//        foreach ( $buildings as &$building )
//        {
//
//            foreach ( $gzhiAddresses as $gzhiAddress )
//            {
//
//                $edsName = (String) $gzhiAddress->edsAddressName;
//
//                if ( strpos( $building->name, $edsName ) )
//                {
//                    $building->gzhi_address_guid = $gzhiAddress->edsAddressGUID ?? '';
//
//                    $building->save();
//
//                    $i ++;
//
//                    continue 2;
//                }
//
//            }
//        }

            foreach ( $gzhiAddresses as $gzhiAddress )
            {
                if(in_array($gzhiAddress->edsAddressGUID, $gArray)) continue;

                $dataArray[ $i ][ 'edsAddressGUID' ] = (string) $gzhiAddress->edsAddressGUID ?? '';

                $dataArray[ $i ][ 'edsFIASAddressGUID' ] = (string) $gzhiAddress->edsFIASAddressGUID ?? '';

                $dataArray[ $i ][ 'edsAddressType' ] = (string) $gzhiAddress->edsAddressType ?? '';

                $dataArray[ $i ][ 'edsOKTMO' ] = (string) $gzhiAddress->edsOKTMO ?? '';

                $dataArray[ $i ][ 'edsAddressName' ] = (string) $gzhiAddress->edsAddressName ?? '';

                $dataArray[ $i ][ 'edsRegion' ] = (string) $gzhiAddress->edsRegion ?? '';

                $dataArray[ $i ][ 'edsArea' ] = (string) $gzhiAddress->edsArea ?? '';

                $dataArray[ $i ][ 'edsCity' ] = (string) $gzhiAddress->edsCity ?? '';

                $dataArray[ $i ][ 'edsStreet' ] = (string) $gzhiAddress->edsStreet ?? '';

                $dataArray[ $i ][ 'edsEstStatus' ] = (string) $gzhiAddress->edsEstStatus ?? '';

                $dataArray[ $i ][ 'edsHouseNum' ] = (string) $gzhiAddress->edsHouseNum ?? '';

                $dataArray[ $i ][ 'edsHouseType' ] = (string) $gzhiAddress->edsHouseType ?? '';

                if(isset($gzhiAddress->edsOrgManager))
                {

                    $dataArray[ $i ][ 'edsOrgGUID' ] = (string) $gzhiAddress->edsOrgManager->edsOrg->edsOrgGUID ?? '';

                    $dataArray[ $i ][ 'edsName' ] = (string) $gzhiAddress->edsOrgManager->edsOrg->edsName ?? '';

                    $dataArray[ $i ][ 'edsFullName' ] = (string) $gzhiAddress->edsOrgManager->edsOrg->edsFullName ?? '';

                    $dataArray[ $i ][ 'edsAddress' ] = (string) $gzhiAddress->edsOrgManager->edsOrg->edsAddress ?? '';

                    $dataArray[ $i ][ 'edsAddressJur' ] = (string) $gzhiAddress->edsOrgManager->edsOrg->edsAddressJur ?? '';

                    $dataArray[ $i ][ 'edsAddressDisp' ] = (string) $gzhiAddress->edsOrgManager->edsOrg->edsAddressDisp ?? '';

                    $dataArray[ $i ][ 'edsTypeOrg' ] = (string) $gzhiAddress->edsOrgManager->edsOrg->edsTypeOrg ?? '';
                }

                $gArray[] = $gzhiAddress->edsAddressGUID;

                $i ++;

            }

        }

        $headersArray = array( 'edsAddressGUID', 'edsFIASAddressGUID', 'edsAddressType', 'edsOKTMO', 'edsAddressName', 'edsRegion'
        , 'edsArea'
        , 'edsCity'
        , 'edsStreet'
        , 'edsEstStatus'
        , 'edsHouseNum'
        , 'edsHouseType',
            'edsOrgGUID',
            'edsName',
            'edsFullName',
            'edsAddress',
            'edsAddressJur',
            'edsAddressDisp',
            'edsTypeOrg'
            );

        $this->generateCSV( $headersArray, $dataArray, $l );

        echo $i;

    }

    public function handleAddresses()
    {
        $array = array();
        for($i = 5; $i<=130; $i+=5)
        {
            $fileName = 'files/ram_addr_for_upload' . $i . '.csv';

            $csvData = \Illuminate\Support\Facades\File::get( storage_path( $fileName ) );

            $lines = explode( PHP_EOL, $csvData );

            $j=0;
            foreach ( $lines as $line )
            {
                if ( $j > 0 )
                {
                    $d = str_getcsv( $line );
                    $array[$d[0]] = str_getcsv( $line );
                }

                $j ++;
            }
        }

        dd(count($array));

        foreach ($array as $one)
        {

        }
    }


    public function getOrgList ()
    {

        $packGuid = Uuid::generate();

        $soapAction = $this->soapGetStateAction;

        $gzhiApiProvider = GzhiApiProvider::whereName( 'Раменское' )
            ->first();

        $orgGuid = $gzhiApiProvider->org_guid;

        $username = $gzhiApiProvider->login;

        $password = $gzhiApiProvider->password;

        $accessData = $username . ':' . $password;

        $packDate = date( 'Y-m-d\TH:i:s' );

        $data = <<<SOAP
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:eds="http://ais-gzhi.ru/schema/integration/eds/" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
    <soapenv:Header/>
        <soapenv:Body>
            <eds:getStateDSRequest Id="?" eds:version="{$this->apiVersion}">
            <eds:Header>
             <!--  You may enter the following 3 items in any order  -->
            <eds:OrgGUID>$orgGuid</eds:OrgGUID>
            <eds:PackGUID>$packGuid</eds:PackGUID>
            <eds:PackDate>$packDate</eds:PackDate>
            </eds:Header>
            <eds:PackGUID>49b4ab69-b2fd-21e9-9c02-ddd67477fd67</eds:PackGUID>
            </eds:getStateDSRequest>
        </soapenv:Body>
</soapenv:Envelope>
SOAP;

        $curl = $this->proceedCurl( $accessData, $data, $soapAction );

        $response = curl_exec( $curl );


        $status_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

        curl_close( $curl );

        if ( $status_code != 200 )
        {
            $this->errorMessage .= "CURL status: $status_code; ";
            $log = \App\Models\Log::create( [
                'text' => $this->errorMessage
            ] );

            $log->save();
            return false;
        }

        $response = preg_replace( "/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response );

        $xml = new \SimpleXMLElement( $response );

        if ( isset( $xml->faultstring ) )
        {
            $this->errorMessage .= $xml->faultstring;
            $log = \App\Models\Log::create( [
                'text' => $this->errorMessage
            ] );

            $log->save();
        }

        if ( ! isset( $xml->soapenvBody ) )
        {
            $this->errorMessage .= " SOAP structure error; ";
            $log = \App\Models\Log::create( [
                'text' => $this->errorMessage
            ] );

            $log->save();
            return false;
        }

        $gzhiAddresses = $xml->soapenvBody->edsgetStateDSResult->edsGetNsiResult->edsOrgs;

        $dataArray = [];
        $i = 0;

        foreach ( $gzhiAddresses as $gzhiAddress )
        {

            $dataArray[ $i ][ 'edsOrgGUID' ] = (string) $gzhiAddress->edsOrgGUID;
            $dataArray[ $i ][ 'edsFullName' ] = (string) $gzhiAddress->edsFullName;
            $dataArray[ $i ][ 'edsName' ] = (string) $gzhiAddress->edsName;
            $dataArray[ $i ][ 'edsAddress' ] = (string) $gzhiAddress->edsAddress;
            $dataArray[ $i ][ 'edsAddressJur' ] = (string) $gzhiAddress->edsAddressJur;

            $i ++;

        }

        $headersArray = array( 'edsOrgGUID', 'edsFullName', 'edsName', 'edsAddress', 'edsAddressJur' );

        $this->generateCSV( $headersArray, $dataArray );

    }

    private function generateCSV ( array $headersArray, array $dataArray, $index = 1 )
    {
        $filename = 'ram_addr_for_upload' . $index . '.csv';

        $output = fopen( storage_path() . '/files/' . $filename, 'w' );

        fputcsv( $output, $headersArray );

        foreach ( $dataArray as $row )
        {
            fputcsv( $output, $row );
        }
        fclose( $output );
    }

    private function proceedCurl ( $accessData, $data, $soapAction )
    {
        try
        {
            $headers = [
                'Content-type: text/xml',
                'Charset: utf-8',
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

            $log = \App\Models\Log::create( [
                'text' => $e->getMessage()
            ] );

            $log->save();

        }
    }

    public function exportGzhiTickets ()
    {

        $ticketsCount = 0;

        $gzhiProviders = GzhiApiProvider::get();

        $packDate = Carbon::now()
            ->format( 'Y-m-d\TH:i:s' );

        $changeFromDate = Carbon::now()
            ->subDays( 1 )
            ->format( 'Y-m-d\TH:i:s' );

        foreach ( $gzhiProviders as $gzhiProvider )
        {

            $packGuid = Uuid::generate();

            $username = $gzhiProvider->login;

            $password = $gzhiProvider->password;

            $accessData = $username . ':' . $password;

            $data = <<<SOAP
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:eds="http://ais-gzhi.ru/schema/integration/eds/" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
   <soapenv:Header/>
   <soapenv:Body>
      <eds:exportAppealRequest Id="?" eds:version="{$this->apiVersion}">
         <eds:Header>
            <eds:OrgGUID>{$gzhiProvider->org_guid}</eds:OrgGUID>
            <eds:PackGUID>$packGuid</eds:PackGUID>
            <eds:PackDate>$packDate</eds:PackDate>
         </eds:Header>  
         <eds:ChangeFromDate>$changeFromDate</eds:ChangeFromDate>
         <eds:WithActions>true</eds:WithActions>
      </eds:exportAppealRequest>
   </soapenv:Body>
</soapenv:Envelope>
SOAP;

            $curl = $this->proceedCurl( $accessData, $data, $this->soapExportAction );

            $response = curl_exec( $curl );

            if ( $response == "" ) continue;

            $status_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

            curl_close( $curl );

            if ( $status_code != 200 )
            {
                $this->errorMessage .= "CURL status: $status_code; ";
                $log = \App\Models\Log::create( [
                    'text' => $this->errorMessage
                ] );

                $log->save();
                continue;
            }

            $response = preg_replace( "/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response );

            $xml = new \SimpleXMLElement( $response );

            if ( ! isset( $xml->soapenvBody->Success->PackGUID ) ) continue;

            $sendingPackGUID = (string) $xml->soapenvBody->Success->PackGUID;

            if ( isset( $xml->faultstring ) )
            {
                $this->errorMessage .= $xml->faultstring;
                $log = \App\Models\Log::create( [
                    'text' => $this->errorMessage
                ] );

                $log->save();
            }

            $gzhiRequest = new GzhiRequest();

            $gzhiRequest->fill( [
                'Action' => $this->soapExportAction,
                'OrgGUID' => $gzhiProvider->org_guid,
                'PackGUID' => $sendingPackGUID,
                'PackDate' => $packDate,
                'Status' => ( $this->errorMessage == '' ) ? GzhiRequest::GZHI_REQUEST_STATUS_IN_WORK : GzhiRequest::GZHI_REQUEST_STATUS_ERROR,
                'Error' => $this->errorMessage,
                'gzhi_api_provider_id' => $gzhiProvider->id
            ] );

            $gzhiRequest->save();

            $ticketsCount ++;
        }

        $logText = "Заявок обработано: $ticketsCount; \n $this->errorMessage \n";

        echo $logText;

        $log = \App\Models\Log::create( [
            'text' => $logText
        ] );

        $log->save();

    }

    public function fillExportedTickets ()
    {

        $gzhiRequests = GzhiRequest::where( [
            'Status' => GzhiRequest::GZHI_REQUEST_STATUS_IN_WORK,
            'Action' => $this->soapExportAction
        ] )
            ->get();

        $ticketsCount = 0;

        if ( count( $gzhiRequests ) )
        {
            foreach ( $gzhiRequests as $gzhiRequest )
            {

                $secondPackGuid = Uuid::generate();

                $packDate = Carbon::now()
                    ->format( 'Y-m-d\TH:i:s' );

                $username = $gzhiRequest->gzhiApiProvider->login;

                $password = $gzhiRequest->gzhiApiProvider->password;

                $accessData = $username . ':' . $password;

                $secondData = <<<SOAP
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:eds="http://ais-gzhi.ru/schema/integration/eds/" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
    <soapenv:Header/>
        <soapenv:Body>
            <eds:getStateDSRequest Id="?" eds:version="{$this->apiVersion}">
            <eds:Header>
            <eds:OrgGUID>{$gzhiRequest->gzhiApiProvider->org_guid}</eds:OrgGUID>
            <eds:PackGUID>$secondPackGuid</eds:PackGUID>
            <eds:PackDate>$packDate</eds:PackDate>
            </eds:Header>
            <eds:PackGUID>$gzhiRequest->PackGUID</eds:PackGUID>
            </eds:getStateDSRequest>
        </soapenv:Body>
</soapenv:Envelope>
SOAP;

                $curl = $this->proceedCurl( $accessData, $secondData, $this->soapGetStateAction );

                $response = curl_exec( $curl );

                $status_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

                if ( $status_code != 200 )
                {
                    $this->errorMessage .= "CURL status: $status_code; ";
                    $log = \App\Models\Log::create( [
                        'text' => $this->errorMessage
                    ] );

                    $log->save();
                    continue;
                }

                $response = preg_replace( "/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response );

                $xml = new \SimpleXMLElement( $response );

                if ( isset( $xml->faultstring ) )
                {
                    $this->errorMessage .= $xml->faultstring;
                    $log = \App\Models\Log::create( [
                        'text' => $this->errorMessage
                    ] );

                    $log->save();
                    continue;
                }

                if ( ! isset( $xml->soapenvBody->edsgetStateDSResult->edsAppealResult ) ) continue;

                $gzhiTickets = $xml->soapenvBody->edsgetStateDSResult->edsAppealResult->edsAppeal;

                if ( count( $gzhiTickets ) )
                {
                    foreach ( $gzhiTickets as $gzhiTicket )
                    {
                        try
                        {
                            $gzhiTicketInformation = $gzhiTicket->edsAppealInformation;

                            if ( $gzhiTicketInformation->edsIsEDS == 'true' ) continue;

                            $orgGUID = (string) $gzhiTicketInformation->edsOrgGUID;

                            $management = Management::where( 'gzhi_guid', $orgGUID )
                                ->first();

                            if ( ! $management ) continue;

                            $ticket = Ticket::where( 'gzhi_appeal_number', (string) $gzhiTicket->edsAppealNumber )
                                ->first();

                            if ( ! $ticket )
                            {

                                $status = Status::where( 'gzhi_status_code', $gzhiTicketInformation->edsStatus )
                                    ->first();

                                if ( ! $status )
                                {
                                    $status = Status::where( 'status_code', 'draft' )
                                        ->first();
                                }

                                $addressGUID = (string) $gzhiTicketInformation->edsAddressGUID;

                                $building = Building::where( 'fais_address_guid', $addressGUID )
                                    ->first();

                                if ( ! $building ) continue;

                                $type = Type::where( 'gzhi_code', (string) $gzhiTicketInformation->edsKindAppeal )
                                    ->first();

                                if ( ! $type ) continue;

                                $ticket = new Ticket();

                                $ticket->transferred_at = Carbon::parse( (string) $gzhiTicketInformation->edsCreationDate )
                                    ->format( 'Y-m-d H:i:s' );

                                $ticket->status_code = $status->status_code ?? '';

                                $ticket->author_id = env( 'MOSREG_USER_ID' ) ?? $management->author_id ?? 1;

                                $ticket->status_name = $status->status_name ?? '';

                                $ticket->gzhi_number_eds = (string) $gzhiTicketInformation->edsNumberReg;

                                $ticket->gzhi_appeal_number = (string) $gzhiTicket->edsAppealNumber;

                                $ticket->lastname = (string) $gzhiTicketInformation->edsInitiator->edsName ?? '';

                                $ticket->type_id = $type->id ?? null;

                                $ticket->building_id = $building->id ?? null;

                                $ticket->place_id = 1;

                                $ticket->provider_id = 1;

                                $customer = Customer::where( 'phone', $gzhiTicketInformation->edsInitiator->edsPhone )
                                    ->first();

                                if ( $customer )
                                {
                                    $phone = $gzhiTicketInformation->edsInitiator->edsPhone;
                                } else
                                {
                                    $phone = '1111111111';
                                }

                                $ticket->phone = $phone;

                                $ticket->text = (string) $gzhiTicketInformation->edsText . ( isset( $gzhiTicketInformation->edsAddressNote ) ? '. ' . (string) $gzhiTicketInformation->edsAddressNote : '' );

                                $ticket->deadline_execution = Carbon::parse( (string) $gzhiTicketInformation->edsCreationDateedsDateNormative )
                                    ->format( 'Y-m-d H:i:s' );

                                $ticket->rate_comment = (string) $gzhiTicketInformation->edsAnswer;

                                $ticket->vendor_id = Vendor::EAIS_VENDOR_ID;

                                $ticket->save();

                                if ( isset( $gzhiTicketInformation->edsInitiatorFiles )
                                    && count( $gzhiTicketInformation->edsInitiatorFiles )
                                    && isset( $gzhiTicketInformation->edsInitiatorFiles->edsName )
                                    && isset( $gzhiTicketInformation->edsInitiatorFiles->edsAttachmentGUID ) )
                                {

                                    foreach ( $gzhiTicketInformation->edsInitiatorFiles as $edsInitiatorFile )
                                    {
                                        $edsFileName = (string) $edsInitiatorFile->edsName;
                                        $edsAttachmentGUID = (string) $edsInitiatorFile->edsAttachmentGUID;
                                        $this->curlGetFile( $edsFileName, $edsAttachmentGUID, $ticket->id, $accessData, $gzhiRequest->gzhiApiProvider->org_guid );
                                    }
                                }

                                if ( $management )
                                {

                                    $ticketManagement = new TicketManagement();

                                    $ticketManagement->ticket_id = $ticket->id;

                                    $ticketManagement->management_id = $management->id;

                                    $ticketManagement->status_code = $status->status_code ?? '';

                                    $ticketManagement->status_name = $status->status_name ?? '';

                                    $ticketManagement->save();

                                }

                                $ticketsCount ++;

                            } else
                            {
                                continue;
                            }

                        }
                        catch ( \Exception $e )
                        {
                            $this->errorMessage .= $e->getTraceAsString();
                            $log = \App\Models\Log::create( [
                                'text' => $this->errorMessage
                            ] );

                            $log->save();
                            continue;
                        }
                    }
                }

                $gzhiRequest->Status = GzhiRequest::GZHI_REQUEST_STATUS_COMPLETE;
                $gzhiRequest->save();
            }

            $logText = "Заявок ЕАИС-экспорт обработано: $ticketsCount; \n $this->errorMessage \n";

            echo $logText;

            $log = \App\Models\Log::create( [
                'text' => $logText
            ] );

            $log->save();
        }

    }

    public function curlGetFile ( $edsFileName, $edsAttachmentGUID, $ticketId, $accessData, $orgGUID )
    {
        try
        {
            $arr = explode( '.', $edsFileName );
            if ( count( $arr ) != 2 )
            {
                return;
            }
            $extension = $arr[ 1 ];

            $headers = [
                'Content-type: application/x-www-form-urlencoded',
                'X-Upload-OrgGUID: ' . $orgGUID
            ];
            $url = $this->fileRestUrl . $edsAttachmentGUID . '?getfile';

            $curl = curl_init();
            curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 1 );
            curl_setopt( $curl, CURLOPT_URL, $url );
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $curl, CURLOPT_USERPWD, $accessData );
            curl_setopt( $curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
            curl_setopt( $curl, CURLOPT_TIMEOUT, 30 );
            curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
            $response = curl_exec( $curl );

            $status_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

            if ( $status_code != 200 )
            {
                $this->errorMessage .= "CURL status: $status_code; ";
                $log = \App\Models\Log::create( [
                    'text' => $this->errorMessage
                ] );

                $log->save();
                return;
            }

            $fileHashName = md5( rand( 1111, 9999 ) ) . md5( rand( 1111, 9999 ) ) . '.' . $extension;

            $path = 'files/' . $fileHashName;

            $destination = storage_path( 'app/' ) . $path;

            $file = fopen( $destination, "w+" );

            fputs( $file, $response );

            fclose( $file );

            $file = File::create( [
                'model_id' => $ticketId,
                'model_name' => 'App\Models\Ticket',
                'path' => $path,
                'name' => $edsFileName
            ] );

            $file->save();

        }
        catch ( \Exception $e )
        {

            $log = \App\Models\Log::create( [
                'text' => $e->getMessage()
            ] );

            $log->save();

            dd( $e->getMessage() );

        }
    }

}
