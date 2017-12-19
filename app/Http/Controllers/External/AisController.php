<?php

namespace App\Http\Controllers\External;

use App\Classes\Gzhi;
use App\Models\Address;
use App\Models\Region;
use Illuminate\Support\MessageBag;
use Webpatser\Uuid\Uuid;

class AisController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
    }

    private function getDate ()
    {
        return date( 'Y-m-d' ) . 'T' . date( 'H:i:s' );
    }

    public function test ()
    {

        $date = self::getDate();

        $orgGuid = '2EC69678-BD7D-11E7-990B-616CC9FAB898';
        $username = 'user_omsu1';
        $password = '6p1484p3';

        $typeGuid = '19A16311-EBF0-4E27-8F64-D470868A5457';
        $addressGuid = 'A6BD8EF6-BA54-11E7-8E30-FE5F11EEAB0E';
        $ukGuid = '355F5138-BB06-11E7-9583-B5CD11EEAB0E';

        $text = 'Проверка связи';

        $data = [
            'Header' => [],
            'Body' => [
                'Header' => [
                    'OrgGUID' => $orgGuid,
                    'PackGUID' => Uuid::generate(),
                    'PackDate' => $date
                ],
                'Appeal' => [
                    'AppealGUID' => Uuid::generate(),
                    'TransportGUID' => Uuid::generate(),
                    'AppealInformation' => [
                        'CreationDate' => $date,
                        'Status' => 30,
                        'Initiator' => [
                            'FIO' => 'Скабелин Дмитрий Сергеевич',
                            'Phone' => '79647269122'
                        ],
                        'TypeAppeal' => 10,
                        'TypeWorkGUID' => $typeGuid,
                        'AddressGUID' => $addressGuid,
                        'Text' => $text,
                        'OrgGUID' => $ukGuid,
                        'NumberReg' => rand( 100, 9999 ),
                        'DateReg' => date( 'Y-m-d' ),
                        'DatePlan' => $date
                    ]
                ]
            ]
        ];

        /*$url = 'https://test-gzhi.eiasmo.ru/eds-service';

        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:eds="http://ais-gzhi.ru/schema/integration/eds/" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
            <soapenv:Header/>
            <soapenv:Body>
                <eds:importAppealRequest eds:version="1.0.0.1">
                    <eds:Header>
                        <eds:OrgGUID>' . $orgGuid . '</eds:OrgGUID>
                        <eds:PackGUID>' . Uuid::generate() . '</eds:PackGUID>
                        <eds:PackDate>' . $date . '</eds:PackDate>
                    </eds:Header>
                    <eds:Appeal>
                        <eds:AppealGUID>' . Uuid::generate() . '</eds:AppealGUID>
                        <eds:TransportGUID>' . Uuid::generate() . '</eds:TransportGUID>
                        <eds:AppealInformation>
                            <eds:CreationDate>' . $date . '</eds:CreationDate>
                            <eds:Status>30</eds:Status>
                            <eds:Initiator>
                                <eds:FIO>Скабелин Дмитрий Сергеевич</eds:FIO>
                                <eds:Phone>79647269122</eds:Phone>
                            </eds:Initiator>
                            <eds:TypeAppeal>10</eds:TypeAppeal>
                            <eds:TypeWorkGUID>' . $typeGuid . '</eds:TypeWorkGUID>
                            <eds:AddressGUID>' . $addressGuid . '</eds:AddressGUID>
                            <eds:Text>' . $text . '</eds:Text>
                            <eds:OrgGUID>' . $ukGuid . '</eds:OrgGUID>
                            <eds:NumberReg>' . Uuid::generate() . '</eds:NumberReg>
                            <eds:DateReg>' . date( 'Y-m-d' ) . '</eds:DateReg>
                            <eds:DatePlan>' . $date . '</eds:DatePlan>
                        </eds:AppealInformation>
                    </eds:Appeal>
                </eds:importAppealRequest>
            </soapenv:Body>
        </soapenv:Envelope>';

        $xml = trim( $xml );*/

        //echo $xml;
        //die;

        //dd( $xml );

        $headers = [
            'Content-type: text/xml',
            'Cache-Control: no-cache',
            //'Content-length: ' . mb_strlen( $xml ),
        ];

        //dd( $headers );

        try
        {

            /*$curl = curl_init();
            curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 1 );
            curl_setopt( $curl, CURLOPT_URL, $url );
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $curl, CURLOPT_USERPWD, $username . ':' . $password ); // username and password - declared at the top of the doc
            curl_setopt( $curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
            curl_setopt( $curl, CURLOPT_TIMEOUT, 30 );
            curl_setopt( $curl, CURLOPT_POST, 1 );
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $xml );
            curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );

            $response = curl_exec( $curl );

            $status_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

            curl_close( $curl );

            //dd( $status_code );
            dd( $response );*/

            $client = new \SoapClient(
                //'https://test-gzhi.eiasmo.ru/eds-service/eds.wsdl',
                'https://juk.eds-juk.ru/ais/eds.wsdl',
                array(
                    'login' => $username,
                    'password' => $password,
                    'connection_timeout' => 30,
                    'trace' => 1,
                    'soap_version' => SOAP_1_2
                )
            );

            //$response = $client->__soapCall( 'ImportAppealData', [ $data ] );

            //dd( $client->__getTypes() );
            //dd( $client->__getFunctions() );

            $response = $client->ImportAppealData( $data );
            //$response = $client->GetNsiDS();
            dd( $response );

        }
        catch ( \Exception $e )
        {
            dd( $e );
        }
        /*catch ( \SoapFault $e )
        {
            dd( $e );
        }*/

    }

    public function sync ()
    {

        $region_id = 5;

        $region = Region::find( $region_id );

        $client = new Gzhi( $region->getGzhiConfig() );

        try
        {
            $response = $client->GetResult( 'b0019410-e4cd-11e7-82b7-05d37e8c944e' );
            foreach ( $response->Addresses as $address )
            {
                $res = Address::create([
                    'guid'          => $address->AddressGUID,
                    'region_id'     => $region_id,
                    'name'          => $address->AddressName
                ]);
                if ( $res instanceof MessageBag )
                {
                    dd( $res );
                }
                $res->save();
            }
        }
        catch ( \SoapFault $e )
        {
            dd( $client->getLastRequest(), $e->faultstring . ':' . $e->faultcode );
        }

    }

}