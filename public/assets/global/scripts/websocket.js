var socket = io( 'https://system.eds-region.ru:8443', { secure: true } );
var number = $( 'meta[name="user-phone"]' ).attr( 'content' ) || null;
var connected = false;
var auth = false;

socket

    .on( 'connect', function ()
    {
        // var person = {
        //     call_id: "1570535499.699941",
        //
        // };
        //
        // var me = Object.create(person);
        //
        // me.call_id = "1570535499.699941";
        // me.call_phone = "9629273386";
        // me.channel = "SIP/m9295070506-0002833d";
        // me.customer = {'name' : 'Магамедов Мугамед Мугамедович Оглымед Мугамедович Оглы'};
        // //me.customer.address = 'Жуковский ул. Дугина, д. 10, кв.100';
        // me.phone_office = "9629273386";
        // me.provider = "!! Перезвон !!";
        // var data = me;
        //
        // var message = '<div class="popup-everyone" call_id="' + data.call_id + '"><div class="row">';
        // if ( data.provider )
        // {
        //     message += '<div class="col-md-10"><span class="popup-provider">' + ( data.provider ) + '</span>';
        // }
        // message += '<div class="popup-phone"> ' + data.call_phone + ' </div></div><div class="col-md-2"><button type="button" class="btn btn-warning btn-small" data-channel="' + data.channel + '" data-call-id="' + data.call_id + '" data-call-phone="' + data.call_phone + '" data-call-description="' + data.provider + '" data-action="pickup"><i class="fa fa-phone"></i></button></div><div class="col-md-12"> ';
        //
        // if ( data.customer )
        // {
        //     if ( data.customer.address )
        //     {
        //         message += '<p>' + data.customer.address + '</p>';
        //     }
        //     if ( data.customer.name )
        //     {
        //         message += '<p>' + data.customer.name + '</p>';
        //     }
        // }
        // message += '</div></div></div>';
        //
        // $('#inner-popup-calls').prepend(message);
        //
        // var callsNumber = $('#number-of-calls-badge').html();
        //
        // $('#number-of-calls-badge').html(++callsNumber);






        console.log( 'socket connected' );
        connected = true;
        $.get( '/id', function ( user_id )
        {
            socket.emit( 'auth', user_id );
        });
    })

    .on( 'disconnect', function ()
    {
        console.log( 'socket disconnected' );
        connected = false;
        auth = false;
    })

    .on( 'auth', function ( user_id, number, tabs_active, tabs_limit )
    {
		$.get( '/id', function ( _user_id )
        {
            if ( _user_id != user_id )
            {
                window.location.href = 'https://www.google.com/search?sxsrf=ACYBGNRR7RGwNpAhyF2ZOnaDAmkvCftFVg%3A1569497500817&ei=nKGMXdLBMcv66QTzrryABw&q=%D0%BA%D0%B0%D0%BA+%D1%81%D1%82%D0%B0%D1%82%D1%8C+%D1%85%D0%B0%D0%BA%D0%B5%D1%80%D0%BE%D0%BC+%D0%B4%D0%BB%D1%8F+%D1%87%D0%B0%D0%B9%D0%BD%D0%B8%D0%BA%D0%BE%D0%B2&oq=%D0%BA%D0%B0%D0%BA+%D1%81%D1%82%D0%B0%D1%82%D1%8C+%D1%85%D0%B0%D0%BA%D0%B5%D1%80%D0%BE%D0%BC+%D0%B4%D0%BB%D1%8F+';
            }
			else
			{
				console.log( 'auth ok', user_id, number, tabs_active, tabs_limit );
				auth = true;
			}
        });
    })

    .on( 'block', function ( tabs_limit )
    {
        window.location.href = '/error/block?tabs_limit=' + tabs_limit;
    })

    .on( 'picked_up', function ( data )
    {
        if ( window.location.pathname == '/tickets/create' )
        {
            window.location.reload();
        }
    })

    .on( 'picked_down', function ( data )
    {
        //
    })

    .on( 'hangup', function ( data )
    {
        console.log(111);
    })

    .on( 'call', function ( data )
    {
        if(window.location.href.indexOf('test=1') != -1)
        {

            var message = '<div class="popup-everyone" call_id="' + data.call_id + '"><div class="row">';
            if ( data.provider )
            {
                message += '<div class="col-md-10"><span class="popup-provider">' + ( data.provider ) + '</span>';
            }
            message += '<div class="popup-phone"> ' + data.call_phone + ' </div></div><div class="col-md-2"><button type="button" class="btn btn-warning btn-small" data-channel="' + data.channel + '" data-call-id="' + data.call_id + '" data-call-phone="' + data.call_phone + '" data-call-description="' + data.provider + '" data-action="pickup"><i class="fa fa-phone"></i></button></div><div class="col-md-12"> ';

            if ( data.customer )
            {
                if ( data.customer.address )
                {
                    message += '<p>' + data.customer.address + '</p>';
                }
                if ( data.customer.name )
                {
                    message += '<p>' + data.customer.name + '</p>';
                }
            }
            message += '</div></div></div>';

            $('#inner-popup-calls').prepend(message);

            var callsNumber = $('#number-of-calls-badge').html();

            $('#number-of-calls-badge').html(++callsNumber);

        }else {
            var message = '';
            if ( data.provider )
            {
                message += '<h2>' + ( data.provider ) + '</h2>'
            }
            message += '<h4 class="bold"><i class="fa fa-phone-square fa-lg"></i> ' + data.call_phone + ' <button type="button" class="btn btn-success btn-sm" data-channel="' + data.channel + '" data-call-id="' + data.call_id + '" data-call-phone="' + data.call_phone + '" data-call-description="' + data.provider + '" data-action="pickup">Забрать</button></h4>';
            if ( data.customer )
            {
                if ( data.customer.address )
                {
                    message += '<div class="small">' + data.customer.address + '</div>';
                }
                if ( data.customer.name )
                {
                    message += '<div class="small">' + data.customer.name + '</div>';
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
        }

    })

    .on( 'stream', function ( data )
    {
        //console.log( 'stream', data || null );
        if ( ! data || ! data.action ) return;
        switch ( data.action )
        {
            case 'intercom':
                initIntercom( data.cam_src || null );
                break;
            case 'create':
                if ( ! ticketsAutoupdate ) return;
                var line = $( '#ticket-management-' + data.ticket_management_id );
                if ( line.length ) return;
                $.post( '/tickets/line/' + data.ticket_management_id,
                    {
                        hide: true
                    },
                    function ( response )
                    {
                        if ( ! response ) return;
                        $( response ).addClass( 'new' ).addClass( 'hidden' ).insertAfter( '#tickets-new-message' );
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
                    //console.log( 'update ticket', $( '#ticket-id' ).val() );
                    if ( $( '#ticket-id' ).val() != data.ticket_id ) return;
                    $( '#ticket-show' ).load( window.location.href );
                }
                else
                {
                    if ( ! ticketsAutoupdate ) return;
                    if ( data.ticket_management_id )
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
                    else if ( data.ticket_id )
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
                }
                break;
            case 'comment':
                if ( ! ticketsAutoupdate ) return;
                if ( $( '#ticket-id' ).val() )
                {
                    if ( $( '#ticket-id' ).val() != data.ticket_id ) return;
                    $.post( '/tickets/comments/' + data.ticket_id,
                        function ( response )
                        {
                            if ( ! response ) return;
                            $( '[data-ticket-comments="' + data.ticket_id + '"]' )
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
                    var isNew = $( '[data-ticket="' + data.ticket_id + '"]' ).hasClass( 'new' );
                    if ( ! lines.length ) return;
                    $.post( '/tickets/comments/' + data.ticket_id,
                        function ( response )
                        {
                            if ( ! response ) return;
                            if ( isNew )
                            {
                                lines
                                    .addClass( 'new' );
                            }
                            else
                            {
                                lines
                                    .removeClass( 'hidden' )
                                    .removeClass( 'new' );
                            }
                            lines
                                .pulsate({
                                    repeat: 3,
                                    speed: 500,
                                    color: '#F1C40F',
                                    glow: true,
                                    reach: 15
                                })
                                .find( '.comments' )
                                .removeClass( 'hidden' )
                                .html( response );
                        }
                    );
                }
                break;
        }
    });
