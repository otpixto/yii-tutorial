var socket = io();
var token = $( 'meta[name="user-token"]' ).attr( 'content' );
var connected = false;
var authorized = false;

socket

    .on( 'connect', function ()
    {
        socket.emit( 'auth', token );
        connected = true;
    })

    .on( 'auth', function ()
    {
        authorized = true;
    })

    .on( 'logout', function ()
    {
        authorized = false;
    })

    .on( 'disconnect', function ()
    {
        connected = false;
        authorized = false;
    })

    .on( 'picked_up', function ( data )
    {


    })

    .on( 'picked_down', function ( data )
    {


    })

    .on( 'call', function ( data )
    {

        
    });