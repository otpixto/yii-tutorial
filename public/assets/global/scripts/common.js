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
						'<div class="modal-body"></div>'+
						'<div class="modal-footer">' +
							'<button type="button" class="btn dark btn-outline" data-dismiss="modal">Закрыть</button>' +
						'</div>'
					'</div>' +
				'</div>' +
			'</div>';
			var _modal = $( html ).attr( 'data-id', id );
			_modal.appendTo( '#modals' );
			callback.call( Modal, _modal );
		}

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
		}
		
		if ( simple )
		{
			setTimeout( function ()
			{
				$( '#modals [data-id="' + id + '"] .select2' ).select2();
				$( '#modals [data-id="' + id + '"] .select2-ajax' ).select2({
                    minimumInputLength: 3,
                    minimumResultsForSearch: 30,
                    ajax: {
                        delay: 450,
                        processResults: function ( data, page )
                        {
                            return {
                                results: data
                            };
                        }
                    }
                });
			}, 300 );
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
	}
	
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

function onPickedUp ( phone )
{

    bootbox.confirm({
        message: 'Перейти к оформлению обращения?',
        size: 'small',
        buttons: {
            confirm: {
                label: '<i class="fa fa-check"></i> Да',
                className: 'btn-success'
            },
            cancel: {
                label: '<i class="fa fa-times"></i> Нет',
                className: 'btn-danger'
            }
        },
        callback: function ( result )
        {
            if ( result )
            {

                window.location.href = '/tickets/create?phone=' + String( phone ).substr( -10 );

            }
        }
    });

};

$( document )

	.ready ( function ()
	{

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $( '.pulsate' ).pulsate({
            color: "#CC0000"
        });

	})

	.on ( 'click', '[data-action="comment"]', function ( e )
	{

		e.preventDefault();
		
		var model_id = $( this ).attr( 'data-model-id' );
		var model_name = $( this ).attr( 'data-model-name' );
        var origin_model_id = $( this ).attr( 'data-origin-model-id' );
        var origin_model_name = $( this ).attr( 'data-origin-model-name' );
		var with_file = $( this ).attr( 'data-file' ) || 0;

		if ( ! model_name || ! model_id ) return;

		$.get( '/comment', {
			model_name: model_name,
			model_id: model_id,
            origin_model_id: origin_model_id,
            origin_model_name: origin_model_name,
            with_file: with_file
		}, function ( response )
		{
			Modal.createSimple( 'Добавить комментарий', response, 'comment' );
		});
	
	})
	
	.on ( 'click', '[data-modal-submit]', function ( e )
	{

		e.preventDefault();
		
		$( '#modals [data-id="' + $( this ).attr( 'data-modal-submit' ) + '"] form' ).submit();
	
	})

    .on ( 'click', '[data-toggle]', function ( e )
    {

        e.preventDefault();

        $( $( this ).attr( 'data-toggle' ) ).toggle();

    })

    .on ( 'submit', '.submit-loading', function ( e )
    {

        $( this ).find( ':submit' ).addClass( 'loading' ).attr( 'disabled', 'disabled' );

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

    });