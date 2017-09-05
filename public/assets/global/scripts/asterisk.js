var socket = io( '//dev.eds-juk.ru:8443', { secure: true } );
var ext_number = $( 'meta[name="user-phone"]' ).attr( 'content' ) || null;

function onPickedUp ( phone )
{
    $.post( '/tickets/create-draft', {
        phone: phone
    });
};

socket

    .on( 'connect', function ()
    {
        console.log( 'socket connected' );
        if ( ext_number )
        {
            socket.emit( 'ext_number', ext_number );
        }
    })

    .on( 'picked_up', function ( phone )
    {
        console.log( 'picked_up', phone );
        $.cookie( 'phone', phone );
        onPickedUp ( phone );
    })

    .on( 'picked_down', function ()
    {
        console.log( 'picked_down' );
        $.removeCookie( 'phone' );
    })

    .on( 'call', function ( phone )
    {
    });