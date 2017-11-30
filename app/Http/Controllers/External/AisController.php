<?php

namespace App\Http\Controllers\External;

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

        /*$data = [
            'Header' => [
                'OrgGUID' => '2EC69678-BD7D-11E7-990B-616CC9FAB898',
                'PackGUID' => 'de78000d-7b18-4bbc-8bdf-2b67f5baf60f',
                'PackDate' => date( 'Y-m-d' ) . 'T' . date( 'H:i:s' )
            ],
            'Appeal' => [
                'AppealGUID' => '53C144D6-C46D-11E7-B5A7-EC105E66D70E',
                'TransportGUID' => '573ED042-C46D-11E7-B64D-B1452EC1DEBC',
                'AppealInformation' => [
                    'CreationDate' => date( 'Y-m-d' ) . 'T' . date( 'H:i:s' ),
                    'Status' => 30,
                    'Initiator' => [
                        'FIO' => 'Скабелин Дмитрий Сергеевич',
                        'Mail' => 'dima@ip-home.net',
                        'Phone' => '79647269122'
                    ],
                    'TypeAppeal' => 10,
                    'TypeWorkGUID' => '19A16311-EBF0-4E27-8F64-D470868A5457',
                    'AddressGUID' => '510B6D36-BA52-11E7-BC29-FE5F11EEAB0E',
                    'Text' => 'Проверка интеграции',
                    'OrgGUID' => '357FC670-BB06-11E7-9583-B5CD11EEAB0E',
                    'NumberReg' => 1,
                    'DateReg' => date( 'Y-m-d' ),
                    'DatePlan' => date( 'Y-m-d' ) . 'T' . date( 'H:i:s' )
                ]
            ]
        ];*/

        $orgGuid = '2EC69678-BD7D-11E7-990B-616CC9FAB898';
        $username = 'user_omsu1';
        $password = '6p1484p3';

        $typeGuid = '19A16311-EBF0-4E27-8F64-D470868A5457';
        $addressGuid = 'A6BD8EF6-BA54-11E7-8E30-FE5F11EEAB0E';
        $ukGuid = '355F5138-BB06-11E7-9583-B5CD11EEAB0E';

        $url = 'https://test-gzhi.eiasmo.ru/eds-service';

        $text = 'Проверка связи';

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

        $xml = trim( $xml );

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

            $curl = curl_init();
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
            dd( $response );

            /*$client = new \SoapClient(
                //'https://test-gzhi.eiasmo.ru/eds-service/eds.wsdl',
                'https://dev.eds-juk.ru/ais/eds.wsdl',
                array(
                    'login' => $login,
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
            //dd( $response );*/

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

}