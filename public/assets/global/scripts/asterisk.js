var socket = io( '//juk.edska.ru:8443', { secure: true } );
var number = $( 'meta[name="user-phone"]' ).attr( 'content' ) || null;

socket

    .on( 'connect', function ()
    {
        console.log( 'socket connected' );
        if ( number )
        {
            socket.emit( 'number', number );
        }
    })

    .on( 'picked_up', function ( phone )
    {
        console.log( 'picked_up', phone );
        $.cookie( 'phone', phone );
        if ( window.location.pathname == '/tickets/create' )
        {
            $( '#phone' ).val( phone ).trigger( 'keyup' );
        }
        $( '#phone-state' ).attr( 'class', 'btn btn-sm btn-warning' );
        $( '#call-phone' ).text( phone );
    })

    .on( 'picked_down', function ()
    {
        var phone = $.cookie( 'phone' );
        console.log( 'picked_down', phone );
        $.removeCookie( 'phone' );
        $( '#phone-state' ).attr( 'class', 'btn btn-sm btn-success' );
        $( '#call-phone' ).text( '' );
    })

    .on( 'call', function ( phone )
    {
    });