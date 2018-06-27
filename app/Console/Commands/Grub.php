<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

class Grub extends Command
{

    protected $signature = 'command:grub';

    protected $description = 'Спиздить все данные у ЕДС КОРОЛЕВ';

    public function __construct ()
    {
        $this->client = new Client();
        parent::__construct();
    }

    public function handle ()
    {

        $api_url = 'https://mo.i-eds.ru/api/';

        $per_page = 1000;

        $headers = [
            'Authorization'         => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiIwYzY5MDNmMi0yOWU3LTQyNmMtOTI4Zi0yYWQzZjk0MWQ2MjciLCJzdWIiOiJtdmVyaW5AbWFpbC5ydSIsInR5cGUiOiJlbWFpbCIsImlhdCI6MTUzMDAyMDMxOCwianRpIjoiMDk4Y2VmYTc4MGRlMDUzYmE1YWJhMjQ1YTE1MDgyZTY2N2U3ZjBiNyJ9.ENhadqF_iqEfaPPCZJ7h23OJTO7jJa9tZXJH-cy5lRE',
            'Content-Type'          => 'application/json',
        ];

        /*$this->info( 'Addresses' );
        $page = 0;
        $pages = null;

        while ( is_null( $pages ) || $pages > $page )
        {

            $this->info( 'Page #' . $page );

            $url = $api_url . 'buildings?pn=' . $page . '&ps=' . $per_page . '&sort=-building_id';

            $response = $this->client->get( $url, [
                'headers' => $headers
            ]);

            $pages = (int) $response->getHeader( 'X-PAGINATION-PAGE-COUNT' )[ 0 ] ?? 0;

            $json_string = $response->getBody();
            file_put_contents( storage_path( 'json/addresses/' . $page . '.json' ), $json_string );

            $this->info( 'Complete' );

            $page ++;

        }*/

        /*$this->info( 'Managements' );

        $url = $api_url . 'companies';

        $response = $this->client->get( $url, [
            'headers' => $headers
        ]);

        $json_string = $response->getBody();
        file_put_contents( storage_path( 'json/managements.json' ), $json_string );

        $this->info( 'Complete' );*/

        /*$this->info( 'Customers' );
        $page = 0;
        $pages = null;

        while ( is_null( $pages ) || $pages > $page )
        {

            $this->info( 'Page #' . $page );

            $url = $api_url . 'clients/table?pn=' . $page . '&ps=' . $per_page . '&sort=-user_id';

            $response = $this->client->get( $url, [
                'headers' => $headers
            ]);

            $pages = (int) $response->getHeader( 'X-PAGINATION-PAGE-COUNT' )[ 0 ] ?? 0;

            $json_string = $response->getBody();
            file_put_contents( storage_path( 'json/customers/' . $page . '.json' ), $json_string );

            $this->info( 'Complete' );

            $page ++;

        }*/

        $this->info( 'Tickets' );
        $page = 0;
        $pages = null;

        while ( is_null( $pages ) || $pages > $page || $pages > 10 )
        {

            $this->info( 'Page #' . $page );

            $url = $api_url . 'issues?pn=' . $page . '&ps=' . $per_page . '&sort=-id';

            $response = $this->client->get( $url, [
                'headers' => $headers
            ]);

            $pages = (int) $response->getHeader( 'X-PAGINATION-PAGE-COUNT' )[ 0 ] ?? 0;

            $json_string = $response->getBody();
            file_put_contents( storage_path( 'json/tickets/' . $page . '.json' ), $json_string );

            $this->info( 'Complete' );

            $page ++;

        }

    }

}
