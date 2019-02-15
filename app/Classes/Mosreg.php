<?php

namespace App\Classes;

class Mosreg
{

    const URL = 'https://eds.mosreg.ru';

    private $curl = false;

    private $statuses = [
        'IN_WORK' => 'В работе',
        'ANSWERED' => 'Ожидает подтверждения',
        'EXPIRED' => 'Срок превышен',
        'GZI_REMEDY' => 'Контроль ГЖИ: Устранение',
        'GZI_REMEDY_ANSWER' => 'Контроль ГЖИ: Получен ответ УК',
        'GZI_EXPIRED' => 'Контроль ГЖИ: Просрочено',
        'GZI_EXTRA_AUDIT' => 'Контроль ГЖИ: Внеплановая проверка',
        'UNSATISFIED' => 'Требуется доработка',
        'SOLVED' => 'Закрыто',
        'UNSATISFIED_SENDED_TO_DD' => 'Несогласие жителя. Отправлено в Добродел.',
    ];

    public function __construct ( $username, $password )
    {

        $cookie_file = storage_path( 'mosreg.txt' );

        $this->curl = curl_init();

        curl_setopt( $this->curl, CURLOPT_URL, self::URL . '/login' );
        $data = [
            'login-form-email' => $username,
            'login-form-password' => $password,
        ];
        curl_setopt( $this->curl, CURLOPT_POST, true );
        curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $this->curl, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER, true );
        //curl_setopt( $this->curl, CURLOPT_FOLLOWLOCATION, false );
        curl_setopt( $this->curl, CURLOPT_COOKIEJAR, $cookie_file );
        curl_setopt( $this->curl, CURLOPT_COOKIEFILE, $cookie_file );

        curl_exec( $this->curl );
        $err = curl_error( $this->curl );

        if ( $err )
        {
            die( 'ERROR: ' . $err );
        }

    }

    public function normalizeAddress ( $address )
    {
        return trim( preg_replace( '/\s{2,}/U', ' ', preg_replace( '/г\.|ул\.|д\.|\,/iU', '', str_replace( ' к. ', 'к', $address ) ) ) );
    }

    public function searchAddress ( $address, $normalize = false )
    {
        if ( $normalize )
        {
            $address = $this->normalizeAddress( $address );
        }
        curl_setopt( $this->curl, CURLOPT_URL, self::URL . '/api/company/search?address=' . urlencode( $address ) );
        curl_setopt( $this->curl, CURLOPT_POST, false );
        $response = curl_exec( $this->curl );
        $err = curl_error( $this->curl );
        if ( $err )
        {
            die( 'ERROR: ' . $err );
        }
        $data = json_decode( $response );
        return $data->value->list;
    }

    public function getTickets ( $page = 1 )
    {
        curl_setopt( $this->curl, CURLOPT_URL, self::URL . '/claims' );
        curl_setopt( $this->curl, CURLOPT_POST, false );
        $response = curl_exec( $this->curl );
        $err = curl_error( $this->curl );
        if ( $err )
        {
            die( 'ERROR: ' . $err );
        }
        $re = '/<tr class=\".*\" data-claim=\"(.*)\" .*>\s*<td class=".*"><\/td>\s*<td class=".*">\s*<span>(.*)<\/span>\s*<\/td>\s*<td class=".*">\s*<span>(.*)<\/span>\s*<\/td>\s*<td class=".*">\s*<span>(.*)<\/span>\s*<\/td>\s*<td class=".*">\s*<span>(.*)<\/span>\s*<\/td>\s*<td class=".*">\s*<span>(.*)<\/span>\s*<\/td>\s*<td class=".*">\s*<span>(.*)<\/span>\s*<\/td>\s*<td class=".*">\s*<span>(.*)<\/span>\s*<\/td>\s*<td class=".*">\s*<span>(.*)<\/span>\s*<\/td>\s*<td class=".*">\s*<span>(.*)<\/span>\s*<\/td>\s*<td class=".*">\s*<span.*><\/span>\s*<\/td>\s*<td class=".*"><\/td>\s*<\/tr>/m';
        $data = [];
        preg_match_all( $re, $response, $matches, PREG_SET_ORDER );
        foreach ( $matches as $i => $row )
        {
            $id = $row[ 1 ];
            $data [ $id ] = [
                'number' => $row[ 2 ],
                'created_at' => $row[ 3 ],
                'management' => $row[ 4 ],
                'customer_name' => $row[ 5 ],
                'customer_type' => $row[ 6 ],
                'building_name' => $row[ 7 ],
                'type_name' => $row[ 8 ],
                'updated_at' => $row[ 9 ],
                'status_name' => $row[ 10 ],
            ];
            $data[ $id ][ 'status_code' ] = array_search( $data[ $id ][ 'status_name' ], $this->statuses );
        }
        return $data;
    }

    public function getTicket ( $id )
    {
        curl_setopt( $this->curl, CURLOPT_URL, self::URL . '/claim/' . $id );
        curl_setopt( $this->curl, CURLOPT_POST, false );
        $response = curl_exec( $this->curl );
        $err = curl_error( $this->curl );
        if ( $err )
        {
            die( 'ERROR: ' . $err );
        }
        return $response;
    }

    public function createTicket ( array $data = [] )
    {
        /*$data = [
            'operator-claim-form-username'          => 'Иванов Иван Иванович',
            'operator-claim-form-email'             => 'test@test.ru',
            'operator-claim-form-phone'             => '74951234567',
            'companyId'                             => 18823,
            'addressId'                             => 507003,
            'operator-claim-form-flat'              => 666,
            'categoryId'                            => 1,
            'operator-claim-form-text'              => 'test',
            'files'                                 => null,
        ];*/
        curl_setopt( $this->curl, CURLOPT_URL,self::URL . '/api/operator/claim' );
        curl_setopt( $this->curl, CURLOPT_POST, true );
        curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $data );
        $response = curl_exec( $this->curl );
        $err = curl_error( $this->curl );
        if ( $err )
        {
            die( 'ERROR: ' . $err );
        }
        $data = json_decode( $response );
        return $data ? $data->value : false;
    }

    public function addComment ( $mosreg_id, $comment )
    {
        curl_setopt( $this->curl, CURLOPT_URL,self::URL . '/api/claim/comment/' . $mosreg_id );
        curl_setopt( $this->curl, CURLOPT_POST, true );
        //curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $data );
    }

    public function changeStatus ( $mosreg_id, $status )
    {
        if ( ! isset( $this->statuses[ $status ] ) )
        {
            die( 'ERROR: Incorrect Status' );
        }
        switch ( $status )
        {
            case 'IN_WORK':
                curl_setopt( $this->curl, CURLOPT_URL,self::URL . '/api/claim/towork/' . $mosreg_id );
                curl_setopt( $this->curl, CURLOPT_POST, true );
                $response = curl_exec( $this->curl );
                $err = curl_error( $this->curl );
                if ( $err )
                {
                    die( 'ERROR: ' . $err );
                }
                $data = json_decode( $response );
                return $data && $data->result == 'OK' ? true : false;
                break;
        }
    }

    public function __destruct ()
    {
        curl_close ( $this->curl );
    }

}