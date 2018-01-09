var socket = io( '//system.eds-region.ru:8443', { secure: true } );
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
        if ( window.location.pathname == '/tickets/create' && $( '#phone' ).length )
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
    })

    .on( 'stream', function ( data )
    {
        if ( ! data || ! data.action ) return;
        switch ( data.action )
        {
            case 'create':
                var line = $( '#ticket-' + data.id );
                if ( line.length ) return;
                $.post( '/tickets/line/' + data.id,
                    {
                        hide: true
                    },
                    function ( response )
                    {
                        if ( ! response ) return;
                        $( response ).insertAfter( '#tickets-new-message' );
                        var count = $( '#tickets .tickets.hidden' ).length;
                        $( '#tickets-new-count' ).text( count );
                        if ( count )
                        {
                            $( '#tickets-new-message' ).removeClass( 'hidden' ).pulsate({
                                repeat: 3,
                                speed: 500,
                                color: '#F1C40F',
                                glow: true,
                                reach: 15
                            });
                        }
                        else
                        {
                            $( '#tickets-new-message' ).addClass( 'hidden' );
                        }
                    }
                );
                break;
            case 'update':
                var line = $( '#ticket-' + data.id );
                if ( ! line.length ) return;
                $.post( '/tickets/line/' + data.id,
                    function ( response )
                    {
                        if ( ! response ) return;
                        var newLine = $( response );
                        line.replaceWith( newLine );
                        newLine.pulsate({
                            repeat: 3,
                            speed: 500,
                            color: '#F1C40F',
                            glow: true,
                            reach: 15
                        });
                    }
                );
                break;
            case 'comment':
                var line = $( '#ticket-' + data.id );
                if ( ! line.length ) return;
                $.post( '/tickets/comments/' + data.id,
                    function ( response )
                    {
                        if ( ! response ) return;
                        var comments = $( '#ticket-comments-' + data.id );
                        var newComments = $( response );
                        if ( ! comments.length )
                        {
                            newComments.insertAfter( line );
                        }
                        else
                        {
                            comments.replaceWith( newComments );
                        }
                        newComments
                            .pulsate({
                                repeat: 3,
                                speed: 500,
                                color: '#F1C40F',
                                glow: true,
                                reach: 15
                            });
                    }
                );
                break;
        }
    });