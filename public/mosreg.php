<?php

$username = '5010049460';
$password = 'lb52OxCNp';
$cookie_file = __DIR__ . '/cookie.txt';

$ch = curl_init();

curl_setopt( $ch, CURLOPT_URL,'https://eds.mosreg.ru/login' );
curl_setopt( $ch, CURLOPT_POST, true );
$data = [
    'login-form-email' => '5010049460',
    'login-form-password' => 'lb52OxCNp',
];
curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
//curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie_file );
curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie_file );

curl_exec( $ch );
$err = curl_error( $ch );

if ( $err )
{
    die( 'ERROR: ' . $error );
}

if ( isset( $_GET[ 'create' ] ) )
{
    $data = [
        'operator-claim-form-username'          => 'Иванов Иван Иванович',
        'operator-claim-form-email'             => null,
        'operator-claim-form-phone'             => '74951234567',
        'companyId'                             => 18823,
        'addressId'                             => 510583,
        'operator-claim-form-flat'              => '666',
        'categoryId'                            => 1,
        'operator-claim-form-text'              => 'test',
        'files'                                 => null,
    ];
    curl_setopt( $ch, CURLOPT_URL,'https://eds.mosreg.ru/api/operator/claim' );
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
    $response = curl_exec( $ch );
    $err = curl_error( $ch );
    if ( $err )
    {
        die( 'ERROR: ' . $error );
    }
    die( $response );
}

curl_setopt( $ch, CURLOPT_URL,'https://eds.mosreg.ru/claims' );
curl_setopt( $ch, CURLOPT_POST, false );

$response = curl_exec( $ch );
$err = curl_error( $ch );
curl_close ( $ch );

if ( $err )
{
    die( 'ERROR: ' . $error );
}

if ( preg_match_all('#<table[^>]*>(.*?)</table[^>]*>#is', $response, $tables, PREG_PATTERN_ORDER ) )
{
    foreach ( $tables[ 1 ] as $table )
    {
        if ( preg_match_all('#<tr[^>]*>(.*?)</tr[^>]*>#is', $table, $rows, PREG_PATTERN_ORDER ) )
        {
            echo '
            <table border="1">';
            foreach ( $rows[ 1 ] as $row )
            {
                if ( preg_match_all('#<td[^>]*>(.*?)</td[^>]*>#is', $row, $cells, PREG_PATTERN_ORDER ) )
                {
                    echo '
                    <tr>';
                    foreach ( $cells[ 1 ] as $cell )
                    {
                        echo '
                        <td>' . trim( $cell ) . '</td>';
                    }
                    echo '
                    </tr>';
                }
            }
            echo '
            </table>';
        }
    }
}

#echo $response;
#echo htmlspecialchars( $response );