var Modal = {
	
	defaultID: 'modal',
	
	init: function ()
	{
		
		if ( ! $( '#modals' ).length )
		{
			$( '<div id="modals"></div>' ).appendTo( 'body' );
		}
		
	},
	
	create: function ( id )
	{
		
		var id = id || Modal.defaultID;
		
		Modal.init();
		
		if ( $( '#modals [data-id="' + id + '"]' ).length )
		{
			var _modal = $( '#modals [data-id="' + id + '"]' );
		}
		else
		{
			var html = '' +
			'<div class="modal fade" tabindex="-1" role="basic" aria-hidden="true">' +
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
		}
		
		_modal.modal( 'show' );
		
	},
	
	createSimple: function ( title, body, id )
	{
		
		var id = id || Modal.defaultID;
		
		Modal.create( id );
		
		if ( title )
		{
			Modal.setTitle( title, id );
		}
		
		if ( body )
		{
			Modal.setBody( body, id, true );
		}
		
		_modal.modal( 'show' );
		
	},
	
	setTitle: function ( title, id )
	{
		
		var id = id || Modal.defaultID;
		
		if ( ! $( '#modals [data-id="' + id + '"]' ).length )
		{
			return;
		}
		
		$( '#modals [data-id="' + id + '"]' ).find( '.modal-title' ).html( title );
		
	},
	
	setBody: function ( body, id, simple )
	{
		
		var id = id || Modal.defaultID;
		
		if ( ! $( '#modals [data-id="' + id + '"]' ).length )
		{
			return;
		}
				
		$( '#modals [data-id="' + id + '"] .modal-body' ).html( body );
				
		if ( simple && $( '#modals [data-id="' + id + '"] .modal-body form' ).length && ! $( '#modals [data-id="' + id + '"] .modal-footer :submit' ).length )
		{
			Modal.addSubmit( 'Готово', id );
		}
		
	},
	
	addSubmit: function ( value, id )
	{
		
		var id = id || Modal.defaultID;
		
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

$( document )

	.on ( 'click', '[data-action="comment"]', function ( e )
	{

		e.preventDefault();
		
		var model_id = $( this ).attr( 'data-model-id' );
		var model_name = $( this ).attr( 'data-model-name' );
		
		$.get( '/comment', {
			model_name: model_name,
			model_id: model_id
		}, function ( response )
		{
			Modal.createSimple( 'Добавить комментарий', response, 'comment' );
		});
	
	})
	
	.on ( 'click', '[data-modal-submit]', function ( e )
	{

		e.preventDefault();
		
		$( '#modals [data-id="' + $( this ).attr( 'data-modal-submit' ) + '"] form' ).submit();
	
	});