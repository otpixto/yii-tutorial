<?php

namespace Tests\Feature;

use App\Models\Provider;
use Tests\TestCase;

class TicketsTest extends TestCase
{
    private $provider;
    private $user;
    public function setUp ()
    {
        parent::setUp();
        $this->provider = Provider::whereHas( 'users' )->first();
        $this->user = $this->provider->users()->first();
    }

    public function testRedirectIfNotAuth ()
    {
        $response = $this->get( $this->provider->getUrl() );
        $response->assertRedirect( route( 'login' ) );
    }
    public function testRedirectToTicketsList ()
    {
        \Auth::login( $this->user );
        $response = $this->get( $this->provider->getUrl() );
        $response->assertRedirect( route( 'tickets.index' ) );
    }
}
