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

$.fn.selectSegment = function ()
{
    var elements = this;
    $.cachedScript( '/assets/global/plugins/bootstrap-treeview.js' )
        .done( function ( script, textStatus )
        {
            elements.each( function ()
            {
                var obj = $( this );
                var value = $( '<input type="hidden">' ).attr( 'name', obj.attr( 'name' ) ).insertAfter( obj );
                obj.removeAttr( 'name' ).attr( 'readonly', 'readonly' );
                obj.on( 'click focus', function ()
                {
                    Modal.create( 'segment-modal', function ()
                    {
                        Modal.setTitle( 'Выберите сегмент', 'segment-modal' );
                        $.get( '/catalog/segments/tree', function ( response )
                        {
                            var tree = $( '<div></div>' );
                            Modal.setBody( tree, 'segment-modal' );
                            tree.treeview({
                                data: response,
                                onNodeSelected: function ( event, node )
                                {
                                    value.val( node.id );
                                    obj.val( node.text ).removeClass( 'text-muted' );
                                    Modal.hide( 'segment-modal' );
                                },
                                onNodeUnselected: function ( event, node )
                                {
                                    value.val( '' );
                                    obj.val( 'Нажмите, чтобы выбрать' ).addClass( 'text-muted' );
                                }
                            });
                        });
                    });
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
                Modal.onSubmit.call( this, e );
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

function genPassword ( length )
{
    var possible = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    var text = '';
    for ( var i=0; i < length; i++ )
    {
        text += possible.charAt( Math.floor( Math.random() * possible.length ) );
    }
    return text;
}

$( document )

	.ready ( function ()
	{

        $( '.test' ).loading();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var phone = $.cookie ? $.cookie( 'phone' ) : null;
        if ( phone && $( '#phone-state' ).length )
		{
            $( '#phone-state' ).attr( 'class', 'btn btn-sm btn-warning' );
            $( '#call-phone' ).text( phone );
		}

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
    })

    .on ( 'submit', 'form.ajax', function ( e )
    {
    	if ( ! connected ) return;
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
            App.alert({
                container: '#success-message', // alerts parent container(by default placed after the page breadcrumbs)
                place: 'append', // append or prepent in container
                type: 'success',  // alert's type
                message: response.success,  // alert's message
                close: true, // make alert closable
                reset: false, // close all previouse alerts first
                focus: true, // auto scroll to the alert after shown
                closeInSeconds: 5, // auto close after defined seconds
                icon: 'fa fa-check' // put icon before the message
            });
		}
	})

    .on ( 'errors', 'form.ajax', function ( e, response )
    {
        if ( response && response.responseJSON )
        {
            $.each( response.responseJSON, function ( i, error )
            {
                App.alert({
                    container: '#errors-message', // alerts parent container(by default placed after the page breadcrumbs)
                    place: 'append', // append or prepent in container
                    type: 'danger',  // alert's type
                    message: error,  // alert's message
                    close: true, // make alert closable
                    reset: false, // close all previouse alerts first
                    focus: true, // auto scroll to the alert after shown
                    closeInSeconds: 5, // auto close after defined seconds
                    icon: 'fa fa-close' // put icon before the message
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

    .on ( 'click', '[data-pickup]', function ( e )
    {

        e.preventDefault();

        if ( ! confirm( 'Вы уверены?' ) ) return;

        var channel = $( this ).attr( 'data-pickup' );

        if ( ! channel ) return;

        $.post( '/pickup-call', {
            channel: channel
        });

        $( this ).closest( '.bootstrap-growl' ).remove();

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
        var origin_model_id = $( this ).attr( 'data-origin-model-id' );
        var origin_model_name = $( this ).attr( 'data-origin-model-name' );
		var with_file = $( this ).attr( 'data-file' ) || 0;

        var title = $( this ).attr( 'data-title' );

		if ( ! model_name || ! model_id ) return;

		$.get( '/comment', {
			model_name: model_name,
			model_id: model_id,
            origin_model_id: origin_model_id,
            origin_model_name: origin_model_name,
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
    });