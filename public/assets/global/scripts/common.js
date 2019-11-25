$.fn.select2.defaults.set( 'allowClear', true );

$.fn.loading = function ()
{
    $( this.selector ).html( '<div class="progress progress-striped active"><div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">Загрузка...</div></div>' );
};

jQuery.cachedScript = function( url, options ) {

    // Allow user to set any option except for dataType, cache, and url
    options = $.extend( options || {}, {
        dataType: "script",
        cache: true,
        url: url
    });

    // Use $.ajax() since it is more flexible than $.getScript
    // Return the jqXHR object so we can chain callbacks
    return jQuery.ajax( options );
};

$.fn.selectSegments = function ()
{
    var elements = this;
    $.cachedScript( '/assets/global/plugins/bootstrap-treeview.js' )
        .done( function ( script, textStatus )
        {
            elements.each( function ()
            {

                var obj = $( this ).empty();
                var name = obj.attr( 'data-name' );
                var placeholder = obj.attr( 'data-placeholder' );

                var value = obj.attr( 'data-value' ) || '';
                var title = obj.attr( 'data-title' ) || '';

                var inputGroup = $( '<div class="input-group"></div>' ).appendTo( obj );

                var select = $( '<select>' )
                    .attr( 'name', name )
                    .attr( 'data-ajax--url', '/catalog/segments/search' )
                    .addClass( 'form-control' )
                    .appendTo( inputGroup );

                if ( /\[|\]/.test( name ) )
                {
                    select.attr( 'multiple', 'multiple' );
                    if ( ! placeholder )
                    {
                        placeholder = 'Выберите сегменты';
                    }
                }
                else if ( ! placeholder )
                {
                    placeholder = 'Выберите сегмент';
                }

                select
                    .attr( 'placeholder', placeholder )
                    .attr( 'data-placeholder', placeholder );

                if ( value && title )
                {
                    select.append(
                        $( '<option></option>' ).val( value ).text( title )
                    ).val( value );
                }

                var button = $( '<button type="button" class="btn btn-default"><i class="fa fa-plus"></i></button>' )
                    .on( 'click', function ()
                    {
                        Modal.create( 'segment-modal', function ()
                        {
                            Modal.setTitle( 'Выберите сегменты', 'segment-modal' );
                            $.get( '/catalog/segments/tree', function ( response )
                            {
                                var tree = $( '<div></div>' );
                                Modal.setBody( tree, 'segment-modal' );
                                tree.treeview({
                                    data: response,
                                    onNodeSelected: function ( event, node )
                                    {
                                        if ( select.find( 'option[value="' + node.id + '"]' ).length )
                                        {
                                            var values = select.select2( 'val' );
                                            if ( values.indexOf( node.id ) == -1 )
                                            {
                                                values.push( node.id );
                                            }
                                            select.val( values ).trigger( 'change' );
                                        }
                                        else
                                        {
                                            var newOption = new Option( node.name, node.id, true, true );
                                            select.append( newOption ).trigger( 'change' );
                                        }
                                        Modal.hide( 'segment-modal' );
                                    }
                                });
                            });
                        });
                    })
                    .appendTo( inputGroup )
                    .wrap( '<span class="input-group-btn"></span>' );

                select
                    .select2({
                        minimumInputLength: 3,
                        minimumResultsForSearch: 30,
                        ajax: {
                            cache: true,
                            type: 'post',
                            delay: 450,
                            data: function ( term )
                            {
                                var data = {
                                    q: term.term,
                                    provider_id: $( '#provider_id' ).val()
                                };
                                var _data = $( this ).closest( 'form' ).serializeArray();
                                for( var i = 0; i < _data.length; i ++ )
                                {
                                    if ( _data[ i ].name != '_method' )
                                    {
                                        data[ _data[ i ].name ] = _data[ i ].value;
                                    }
                                }
                                return data;
                            },
                            processResults: function ( data, page )
                            {
                                return {
                                    results: data
                                };
                            }
                        }
                    });
            });
        });
};

$.fn.selectBuildings = function ()
{
    var elements = this;
    $.cachedScript( '/assets/global/plugins/select2/js/select2.full.min.js' )
        .done( function ( script, textStatus )
        {
            elements.each( function ()
            {

                var obj = $( this ).empty();
                var name = obj.attr( 'data-name' );
                var placeholder = obj.attr( 'data-placeholder' );

                var inputGroup = $( '<div class="input-group"></div>' ).appendTo( obj );

                var select = $( '<select>' )
                    .attr( 'name', name )
                    .attr( 'data-ajax--url', '/catalog/buildings/search' )
                    .addClass( 'form-control' )
                    .appendTo( inputGroup );

                if ( /\[|\]/.test( name ) )
                {
                    select.attr( 'multiple', 'multiple' );
                    if ( ! placeholder )
                    {
                        placeholder = 'Выберите адреса';
                    }
                }
                else if ( ! placeholder )
                {
                    placeholder = 'Выберите адрес';
                }

                select
                    .attr( 'placeholder', placeholder )
                    .attr( 'data-placeholder', placeholder );

                var button = $( '<button type="button" class="btn btn-default"><i class="fa fa-plus"></i></button>' )
                    .on( 'click', function ()
                    {
                        Modal.create( 'building-modal', function ()
                        {
                            Modal.setTitle( 'Выберите Адреса', 'segment-modal' );
                            $.get( '/catalog/buildings/select', function ( response )
                            {
                                Modal.setBody( response, 'segment-modal' );
                            });
                        });
                    })
                    .appendTo( inputGroup )
                    .wrap( '<span class="input-group-btn"></span>' );

                select
                    .select2({
                        minimumInputLength: 3,
                        minimumResultsForSearch: 30,
                        ajax: {
                            cache: true,
                            type: 'post',
                            delay: 450,
                            data: function ( term )
                            {
                                var data = {
                                    q: term.term,
                                    provider_id: $( '#provider_id' ).val()
                                };
                                var _data = $( this ).closest( 'form' ).serializeArray();
                                for( var i = 0; i < _data.length; i ++ )
                                {
                                    if ( _data[ i ].name != '_method' )
                                    {
                                        data[ _data[ i ].name ] = _data[ i ].value;
                                    }
                                }
                                return data;
                            },
                            processResults: function ( data, page )
                            {
                                return {
                                    results: data
                                };
                            }
                        }
                    });
            });
        });
};

var Modal = {
	
	defaultID: 'modal',
	lastID: null,
	
	init: function ()
	{
		
		if ( ! $( '#modals' ).length )
		{
			$( '<div id="modals"></div>' ).appendTo( 'body' );
		}
		
	},
	
	create: function ( id, callback )
	{
		
		var id = id || Modal.defaultID;

        Modal.lastID = id;
		
		Modal.init();
		
		if ( $( '#modals [data-id="' + id + '"]' ).length )
		{
			var _modal = $( '#modals [data-id="' + id + '"]' );
		}
		else
		{
			var html = '' +
			'<div class="modal fade" role="basic" aria-hidden="true">' +
				'<div class="modal-dialog">' +
					'<div class="modal-content">' +
						'<div class="modal-header">' +
							'<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>' +
							'<h4 class="modal-title"></h4>' +
						'</div>'+
						'<div class="modal-body"></div>' +
						'<div class="modal-footer">' +
							'<button type="button" class="btn dark btn-outline" data-dismiss="modal">Закрыть</button>' +
						'</div>'
					'</div>' +
				'</div>' +
			'</div>';
			var _modal = $( html ).attr( 'data-id', id );
			_modal.appendTo( '#modals' );
		}

        Modal.setBody( '<div class="progress progress-striped active"><div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">Загрузка...</div></div>' );

        callback.call( Modal, _modal );
		_modal.modal( 'show' );
		
	},
	
	createSimple: function ( title, body, id )
	{
		
		var id = id || Modal.defaultID;

		Modal.lastID = id;
		
		Modal.create( id, function ()
		{
            if ( title )
            {
                Modal.setTitle( title, id );
            }
            if ( body )
            {
                Modal.setBody( body, id, true );
            }
		});
		
	},
	
	setTitle: function ( title, id )
	{
		
		var id = id || Modal.lastID || Modal.defaultID;

        Modal.lastID = id;
		
		if ( ! $( '#modals [data-id="' + id + '"]' ).length )
		{
			return;
		}
		
		$( '#modals [data-id="' + id + '"]' ).find( '.modal-title' ).html( title );
		
	},
	
	setBody: function ( body, id, simple )
	{
		
		var id = id || Modal.lastID || Modal.defaultID;

        Modal.lastID = id;
		
		if ( ! $( '#modals [data-id="' + id + '"]' ).length )
		{
			return;
		}
				
		$( '#modals [data-id="' + id + '"] .modal-body' ).html( body );
				
		if ( simple && $( '#modals [data-id="' + id + '"] .modal-body form' ).length && ! $( '#modals [data-id="' + id + '"] .modal-footer :submit' ).length )
		{
			Modal.addSubmit( 'Готово', id );
            $( '#modals [data-id="' + id + '"] .modal-body form' ).submit( function ( e )
            {
                Modal.onSubmit.call( this, e, id );
            });
		}
		
	},
	
	addSubmit: function ( value, id )
	{
		
		var id = id || Modal.lastID || Modal.defaultID;

        Modal.lastID = id;

		if ( ! $( '#modals [data-id="' + id + '"]' ).length )
		{
			return;
		}
		
		$( '#modals [data-id="' + id + '"]' ).find( '.modal-footer' ).append( $( '<button type="submit" class="btn btn green" data-modal-submit="' + id + '"></button>' ).html( value ) );
		
	},
	
	clear: function ()
	{
		$( '#modals' ).empty();
	},

    open: function ( id )
    {
        var id = id || Modal.defaultID;
        Modal.lastID = id;
        if ( $( '#modals [data-id="' + id + '"]' ).length )
        {
            $( '#modals [data-id="' + id + '"]' ).modal( 'show' );
        }
    },

    hide: function ( id )
    {
        var id = id || Modal.defaultID;
        Modal.lastID = id;
        if ( $( '#modals [data-id="' + id + '"]' ).length )
        {
            $( '#modals [data-id="' + id + '"]' ).modal( 'hide' );
        }
    },

    onSubmit: function ( e ) {}
	
};

String.prototype.plural = Number.prototype.plural = function ( a, b, c )
{
    var index = this % 100;
    index = (index >=11 && index <= 14) ? 0 : (index %= 10) < 5 ? (index > 2 ? 2 : index): 0;
    return(this+[a, b, c][index]);
};

if ($.ui && $.ui.dialog && $.ui.dialog.prototype._allowInteraction) {
    var ui_dialog_interaction = $.ui.dialog.prototype._allowInteraction;
    $.ui.dialog.prototype._allowInteraction = function(e) {
        if ($(e.target).closest('.select2-dropdown').length) return true;
        return ui_dialog_interaction.apply(this, arguments);
    };
}

function takeScreenshot ( obj, callback )
{
    html2canvas( obj ).then( function ( canvas )
    {
        //callback.call( obj, canvas );
        callback( canvas );
    });
};

function genPassword ( length )
{
    var possible = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    var text = '';
    for ( var i=0; i < length; i++ )
    {
        text += possible.charAt( Math.floor( Math.random() * possible.length ) );
    }
    return text;
};

function getQueue ( modal )
{
    if ( modal )
    {
        Modal.create( 'asterisk-list', function ()
        {
            Modal.setTitle( 'Очередь звонков' );
            $.post( '/asterisk/queue', function ( response )
            {
                Modal.setBody( response, 'asterisk-list' );
            });
        });
    }
    else
    {
        if ( $( '#queues' ).length && ! $( '#queues' ).hasClass( 'loading' ) )
        {
            $( '#queues' ).addClass( 'loading' );
            $.getJSON( '/asterisk/queue', function ( response )
            {
                if ( response )
                {
                    $( '#queues-count' ).text( response.busy + ' / ' + response.count + ' / ' + response.callers );
                    $( '#queues-info' ).removeClass( 'hidden' );
                    $( '#queues' ).removeClass( 'loading' );
                }
            });
        }
    }
};

function initIntercom ( cam_src )
{
    $( '#intercom' ).removeClass( 'hidden' );
    if ( cam_src )
    {
        $( '#intercom-image' )
            .css({
                'background-image': 'url("' + cam_src + '")',
                'background-size': 'contain'
            })
            .attr( 'data-src', cam_src );
    }
    else
    {
        $( '#intercom-image' )
            .css({
                'background-image': 'url("/images/novideo.png")',
                'background-size': 'auto'
            })
            .removeAttr( 'data-src' );

    }
};

function deinitIntercom ()
{
    $( '#intercom' )
        .addClass( 'hidden' );
};

$( window )
    .on( 'keydown', function ( e )
    {
        if ( e.ctrlKey && e.which == 83 )
        {
            e.preventDefault();
            $( window ).scrollTop( 0 );
            $( '#modal-support' ).modal( 'show' );
            setTimeout( function()
            {
                $( '#support-subject' ).val( '' );
                $( '#support-body' ).val( 'Адрес страницы: ' + window.location.href );
                takeScreenshot( document.querySelector( 'body div.wrapper' ), function ( canvas )
                {
                    canvas.classList.add( 'img-responsive' );
                    canvas.removeAttribute( 'style' );
                    var canvasData = canvas.toDataURL( 'image/png' );
                    $( '#support-data' ).val( canvasData );
                    $( '#screenshot-support' ).html( canvas );
                });
            }, 1 );
        }
    });

$( document )

    .ajaxError( function ( err, err2 )
    {
        if ( err2.status == 401 )
        {
            swal({
                title: 'Ошибка',
                text: 'Требуется авторизация',
                type: 'error',
                allowOutsideClick: true
            });
            setTimeout( function ()
            {
                window.location.href = '/login';
            }, 5000 );
        }
    })

	.ready ( function ()
	{

        /*$( '#intercom' )
            .draggable()
            .pulsate({
                speed: 600,
                color: '#CC0000',
                glow: true,
                reach: 10
            });*/

        $( '.test' ).loading();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

		$( '.form-control:hidden' ).css( 'width', '100%' );

        $( '.toggle' ).each( function ()
        {
            if ( ! $( this ).hasClass( '_collapse' ) && ! $( this ).hasClass( '_expand' ) )
            {
                $( this ).addClass( '_collapse' );
            }
        });

        $( '.select2' ).select2();
        $( '.select2-ajax' ).select2({
            minimumInputLength: 3,
            minimumResultsForSearch: 30,
            ajax: {
                cache: true,
                type: 'post',
                delay: 450,
                data: function ( term )
                {
                    var data = {
                        q: term.term,
                        provider_id: $( '#provider_id' ).val()
                    };
                    var _data = $( this ).closest( 'form' ).serializeArray();
                    for( var i = 0; i < _data.length; i ++ )
                    {
                        if ( _data[ i ].name != '_method' )
                        {
                            data[ _data[ i ].name ] = _data[ i ].value;
                        }
                    }
                    return data;
                },
                processResults: function ( data, page )
                {
                    return {
                        results: data
                    };
                }
            }
        });

	})

    .on( 'click', '[data-empty]', function ( e )
    {
        e.preventDefault();
        var selector = $( this ).data( 'empty' );
        $( selector ).empty().trigger( 'change' );
    })

    .on( 'click', '[data-group-buildings]', function ( e )
    {
        e.preventDefault();
        var selector = $( this ).data( 'group-buildings' );
        Modal.create( 'group-select', function ()
        {
            Modal.setTitle( 'Выберите группу' );
            $.get( '/catalog/buildings_groups/select',
                {
                    selector: selector
                },
                function ( response )
                {
                    Modal.setBody( response );
                }
            );
        });
    })

    .on( 'click', '.executor-toggle', function ( e )
    {
        $( '#executor_name, #executor_phone, #executor_id' ).val( '' ).trigger( 'change' );
    })

    .on( 'click', '[data-group-data]', function ( e )
    {
        e.preventDefault();
        var selector = $( this ).data( 'group-selector' );
        var data = $( this ).data( 'group-data' );
        Modal.hide( 'group-select' );
        $.each( data, function ( key, val )
        {
            if ( ! $( selector ).find( 'option[value="' + key + '"]' ).length )
            {
                var newOption = new Option( val, key, true, true );
                $( selector ).append( newOption );
            }
        });
        $( selector ).trigger( 'change' );
    })

    .on( 'click', '[data-room]', function ( e )
    {
        e.preventDefault();
        var room_id = $( this ).attr( 'data-room' );
        Modal.create( 'room-' + room_id, function ()
        {
            Modal.setTitle( 'Информация о помещении' );
            $.get( '/catalog/rooms/' + room_id + '/info', function ( response )
            {
                Modal.setBody( response );
            });
        });
    })

    .on( 'click', '[data-user]', function ( e )
    {
        e.preventDefault();
        var user_id = $( this ).attr( 'data-user' );
        Modal.create( 'userinfo-' + user_id, function ()
        {
            Modal.setTitle( 'Информация пользователя' );
            $.get( '/profile/info/' + user_id, function ( response )
            {
                Modal.setBody( response );
            });
        });
    })

    .on( 'click', '[data-customer]', function ( e )
    {
        e.preventDefault();
        var customer_id = $( this ).attr( 'data-customer' );
        console.log( customer_id );
    })

    .on( 'click', '[data-customer-lk]', function ( e )
    {
        e.preventDefault();
        var customer_id = $( this ).attr( 'data-customer-lk' );
        console.log( customer_id );
    })

    .on( 'shown.bs.modal', '.modal', function ()
    {
        $( this ).find( '.select2' ).select2();
        $( this ).find( '.select2-ajax' ).select2({
            minimumInputLength: 3,
            minimumResultsForSearch: 30,
            ajax: {
                cache: true,
                type: 'post',
                delay: 450,
                processResults: function ( data, page )
                {
                    return {
                        results: data
                    };
                }
            }
        });
        $( this ).find( '[autofocus]:first' ).focus();
    })

    .on ( 'submit', 'form.ajax', function ( e )
    {
    	if ( ! connected ) return true;
        e.preventDefault();
        var that = $( this );
        var method = that.attr( 'method' ).toLowerCase();
        var url = that.attr( 'action' );
        var modal = that.closest( '.modal' );
        if ( modal.length )
        {
        	modal.find( ':submit' ).attr( 'disabled', 'disabled' ).addClass( 'loading' );
        }
        $.ajax({
            url: url,
            data: new FormData( that[ 0 ] ),
            method: method,
            contentType: false,
            processData: false,
            cache: false,
            success: function ( response )
            {
                modal.find( ':submit' ).removeAttr( 'disabled' ).removeClass( 'loading' );
                modal.modal( 'hide' );
                that.trigger( 'success', response );
            },
            error: function ( response )
            {
                modal.find( ':submit' ).removeAttr( 'disabled' ).removeClass( 'loading' );
                modal.modal( 'hide' );
                that.trigger( 'errors', response );
            }
        });
    })

	.on ( 'success', 'form.ajax', function ( e, response )
	{
        if ( response && response.success )
		{
            swal({
                title: 'Успешно',
                text: response.success,
                type: 'success',
                allowOutsideClick: true
            });
		}
	})

    .on ( 'errors', 'form.ajax', function ( e, response )
    {
        if ( response && response.responseJSON )
        {
            $.each( response.responseJSON, function ( i, error )
            {
                swal({
                    title: 'Ошибка',
                    text: error,
                    type: 'error',
                    allowOutsideClick: true
                });
            });
        }
    })

	.on ( 'click', '#tickets-new-show', function ( e )
	{

        e.preventDefault();

        $( '#tickets .tickets.new.hidden' ).removeClass( 'hidden' ).removeClass( 'new' );
        $( '#tickets .comments.new.hidden' ).removeClass( 'hidden' ).removeClass( 'new' );
        $( '#tickets-new-message' ).addClass( 'hidden' );

	})

    .on ( 'click', '#intercom-close', function ( e )
    {
        e.preventDefault();
        deinitIntercom();
    })

    .on ( 'click', '#intercom-image', function ( e )
    {
        e.preventDefault();
        var url = '/tickets/create';
        if ( $( this ).data( 'src' ) )
        {
            url += '?cam_src=' + $( this ).data( 'src' );
        }
        window.location.href = url;
    })

    .on ( 'click', '[data-action="pickup"]', function ( e )
    {

        e.preventDefault();

        if ( ! confirm( 'Вы уверены?' ) ) return;

        var channel = $( this ).attr( 'data-channel' );
        var call_id = $( this ).attr( 'data-call-id' );
        var call_phone = $( this ).attr( 'data-call-phone' );
        var call_description = $( this ).attr( 'data-call-description' );

        if ( ! channel ) return;

        $.post( '/pickup-call', {
            channel: channel,
            call_id: call_id,
            call_phone: call_phone,
            call_description: call_description
        });

        var callsNumber = $('#number-of-calls-badge').html();

        $('#number-of-calls-badge').html(--callsNumber);

        $( this ).closest( '.popup-everyone' ).remove();

    })

	.on ( 'click', '[data-action="ticket-call"]', function ( e )
	{

        e.preventDefault();

        var phones = $( this ).attr( 'data-phones' ).replace( ';', ',' ).split( ',' );
        var ticket_id = $( this ).attr( 'data-ticket' );

        if ( phones.length > 1 )
		{
            inputOptions = [];
			for ( var i = 0; i < phones.length; i ++ )
			{
                inputOptions.push({
                    text: phones[ i ],
                    value: phones[ i ].replace( /\D/g, '' ).substr( -10 )
				});
			}
            bootbox.prompt({
                title: 'Выберите номер для совершения звонка',
                inputType: 'select',
                inputOptions: inputOptions,
				callback: function ( phone )
				{
                    $.post( '/asterisk/call',
                        {
                            phone: phone,
                            ticket_id: ticket_id
                        },
                        function ( response )
                        {
                            console.log( response );
                        });
				}
            });
		}
		else
		{
            bootbox.confirm({
                message: 'Вы уверены, что хотите позвонить по номеру ' + phones[ 0 ] + '?',
                buttons: {
                    confirm: {
                        label: 'Да',
                        className: 'btn-success'
                    },
                    cancel: {
                        label: 'Нет',
                        className: 'btn-danger'
                    }
                },
                callback: function ( result )
				{
                    if ( result )
					{
                        $.post( '/asterisk/call',
                            {
                                phone: phones[ 0 ].replace( /\D/g, '' ).substr( -10 ),
                                ticket_id: ticket_id
                            },
                            function ( response )
                            {
                                console.log( response );
                            });
					}
                }
            });
		}

	})

	.on ( 'click', '[data-action="comment"]', function ( e )
	{

		e.preventDefault();
		
		var model_id = $( this ).attr( 'data-model-id' );
		var model_name = $( this ).attr( 'data-model-name' );
        var reply_id = $( this ).attr( 'data-reply-id' );
		var with_file = $( this ).attr( 'data-file' ) || 0;

        var title = $( this ).attr( 'data-title' );

		if ( ! model_name || ! model_id ) return;

		$.get( '/comment', {
			model_name: model_name,
			model_id: model_id,
            reply_id: reply_id,
            with_file: with_file
		}, function ( response )
		{
			Modal.createSimple( title || 'Добавить комментарий', response, 'create-comment' );
		});
	
	})

    .on ( 'click', '[data-action="comment_delete"]', function ( e )
    {

        e.preventDefault();

        if ( ! confirm ( 'Вы уверены, что хотите удалить комментарий?' ) ) return;

        var comment_id = $( this ).attr( 'data-id' );

        if ( ! comment_id ) return;

        $( '#comment-' + comment_id ).remove();

        $.post( '/comment/delete', {
            comment_id: comment_id
        });

    })

    .on ( 'click', '[data-action="file"]', function ( e )
    {

        e.preventDefault();

        var model_id = $( this ).attr( 'data-model-id' );
        var model_name = $( this ).attr( 'data-model-name' );

        if ( ! model_name || ! model_id ) return;

        var title = $( this ).attr( 'data-title' );
        var status = $( this ).attr( 'data-status' );

        $.get( '/file', {
            model_name: model_name,
            model_id: model_id,
            status: status
        }, function ( response )
        {
            Modal.createSimple( title || 'Прикрепить файл', response, 'file' );
        });

    })
	
	.on ( 'click', '[data-modal-submit]', function ( e )
	{

		e.preventDefault();
		
		$( '#modals [data-id="' + $( this ).attr( 'data-modal-submit' ) + '"] form' ).submit();
	
	})

    .on ( 'click', '.toggle', function ( e )
    {
        e.preventDefault();
        $( this ).toggleClass( '_collapse' ).toggleClass( '_expand' );
    })

    .on ( 'click', '[data-toggle]', function ( e )
    {

        e.preventDefault();

        $( $( this ).attr( 'data-toggle' ) ).toggleClass( 'hidden' );

    })

    .on ( 'submit', '.submit-loading', function ( e )
    {
        var modal = $( this ).closest( '.modal' );
        if ( modal.length )
        {
            var submit = modal.find( ':submit' );
        }
        else
        {
            var submit = $( this ).find( ':submit' );
        }
        if ( ! submit.length ) return;
        submit.addClass( 'loading' ).attr( 'disabled', 'disabled' );
        setTimeout( function ()
        {
            submit.removeClass( 'loading' ).removeAttr( 'disabled' );
        }, 3000 );
    })

    .on ( 'submit', '[data-confirm]', function ( e )
    {
        if ( ! confirm ( $( this ).attr( 'data-confirm' ) ) )
        {
            e.preventDefault();
            if ( $( this ).hasClass( 'submit-loading' ) )
            {
                $( this ).find( ':submit' ).removeClass( 'loading' ).removeAttr( 'disabled' );
            }
            return false;
        }
        $( this ).trigger( 'confirmed', [ e ] );
    })

    .on ( 'click', 'a[data-confirm]', function ( e )
    {
        if ( ! confirm ( $( this ).attr( 'data-confirm' ) ) ) return false;
        $( this ).trigger( 'confirmed', [ e ] );
    })

    .on ( 'click', '#number-of-calls-button', function ( e )
    {
        $('#popup-calls').toggle();
    });
