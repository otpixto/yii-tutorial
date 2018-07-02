var socket = io( 'https://system.eds-region.ru:8443', { secure: true } );
var number = $( 'meta[name="user-phone"]' ).attr( 'content' ) || null;
var connected = false;

socket

    .on( 'connect', function ()
    {
        console.log( 'socket connected' );
        connected = true;
        if ( number )
        {
            socket.emit( 'number', number );
        }
    })

    .on( 'disconnect', function ()
    {
        console.log( 'socket disconnected' );
        connected = false;
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

    .on( 'call', function ( data )
    {
        var message = '';
        if ( data.message.region )
        {
            message += '<h2>' + ( data.message.region || '-' ) + '</h2>'
        }
        message += '<h4 class="bold"><i class="fa fa-phone-square fa-lg"></i> ' + data.call_phone + ' <button type="button" class="btn btn-success btn-sm" data-pickup="' + data.channel + '">Забрать</button></h4>';
        if ( data.message.customer )
		{
			if ( data.message.customer.address )
			{
				message += '<div class="small">' + data.message.customer.address + '</div>';
			}
			if ( data.message.customer.name )
			{
				message += '<div class="small">' + data.message.customer.name + '</div>';
			}
		}
        $.bootstrapGrowl( message, {
            ele: 'body',
            type: 'info',
            offset: {
                from: 'bottom',
                amount: 20
            },
            align: 'left',
            width: 350,
            delay: 20000,
            allow_dismiss: true,
            stackup_spacing: 10
        });
    })

    .on( 'stream', function ( data )
    {
        if ( ! data || ! data.action ) return;
        //console.log( data );
        switch ( data.action )
        {
            case 'create':
                var line = $( '#ticket-management-' + data.ticket_management_id );
                if ( line.length ) return;
                $.post( '/tickets/line/' + data.ticket_management_id,
                    {
                        hide: true
                    },
                    function ( response )
                    {
                        if ( ! response ) return;
                        $( response ).addClass( 'new' ).insertAfter( '#tickets-new-message' );
                        var count = $( '#tickets .tickets.new.hidden' ).length;
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
                if ( $( '#ticket-id' ).val() )
                {
                    if ( $( '#ticket-id' ).val() != data.ticket_id ) return;
                    $( '#ticket-show' ).load( window.location.href + ' #ticket-show', function ()
                    {
                        // без этого быдлядства не обошлось...
                        if ( $( '#ticket-show #ticket-show' ).length )
                        {
                            $( '#ticket-show' ).replaceWith( $( '#ticket-show #ticket-show' ) );
                        }
                    });
                }
                else if ( data.ticket_management_id )
                {
                    var line = $( '#ticket-management-' + data.ticket_management_id );
                    if ( ! line.length ) return;
                    var isHidden = line.hasClass( 'hidden' );
                    var isNew = line.hasClass( 'new' );
                    $.post( '/tickets/line/' + data.ticket_management_id,
                        {
                            hideComments: true
                        },
                        function ( response )
                        {
                            if ( ! response ) return;
                            var newLine = $( response );
                            line.replaceWith( newLine );
                            if ( isNew )
                            {
                                newLine.addClass( 'new' );
                            }
                            if ( isHidden )
                            {
                                newLine.addClass( 'hidden' );
                            }
                            else
                            {
                                newLine.pulsate({
                                    repeat: 3,
                                    speed: 500,
                                    color: '#F1C40F',
                                    glow: true,
                                    reach: 15
                                });
                            }
                        }
                    );
                }
                else
                {
                    var lines = $( '[data-ticket="' + data.ticket_id + '"]' );
                    if ( ! lines.length ) return;
                    lines.each( function()
                    {
                        var line = $( this );
                        var isHidden = line.hasClass( 'hidden' );
                        var isNew = line.hasClass( 'new' );
                        var ticket_management_id = line.attr( 'data-ticket-management' );
                        $.post( '/tickets/line/' + ticket_management_id,
                            {
                                hideComments: true
                            },
                            function ( response )
                            {
                                if ( ! response ) return;
                                var newLine = $( response );
                                line.replaceWith( newLine );
                                if ( isNew )
                                {
                                    newLine.addClass( 'new' );
                                }
                                if ( isHidden )
                                {
                                    newLine.addClass( 'hidden' );
                                }
                                else
                                {
                                    newLine.pulsate({
                                        repeat: 3,
                                        speed: 500,
                                        color: '#F1C40F',
                                        glow: true,
                                        reach: 15
                                    });
                                }
                            }
                        );
                    });
                }
                break;
            case 'comment':
                if ( $( '#ticket-id' ).val() )
                {
                    if ( $( '#ticket-id' ).val() != data.ticket_id ) return;
                    $.post( '/tickets/comments/' + data.ticket_id,
                        {
                            commentsOnly: true
                        },
                        function ( response )
                        {
                            if ( ! response ) return;
                            var comments = $( '#ticket-comments' );
                            comments
                                .removeAttr( 'class' )
                                .html( response )
                                .pulsate({
                                    repeat: 3,
                                    speed: 500,
                                    color: '#F1C40F',
                                    glow: true,
                                    reach: 15
                                });
                        }
                    );
                }
                else
                {
                    var lines = $( '[data-ticket-comments="' + data.ticket_id + '"]' );
                    var isHidden = $( '[data-ticket="' + data.ticket_id + '"]' ).hasClass( 'hidden' );
                    var isNew = $( '[data-ticket="' + data.ticket_id + '"]' ).hasClass( 'new' );
                    if ( ! lines.length ) return;
                    $.post( '/tickets/comments/' + data.ticket_id,
                        function ( response )
                        {
                            if ( ! response ) return;
                            var newComments = $( response );
                            lines.replaceWith( newComments );
                            if ( isHidden )
                            {
                                newComments.addClass( 'hidden' );
                            }
                            else
                            {
                                newComments.removeClass( 'hidden' );
                            }
                            if ( isNew )
                            {
                                newComments.addClass( 'new' );
                            }
                            else
                            {
                                newComments.removeClass( 'new' );
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
                }
                break;
        }
    });