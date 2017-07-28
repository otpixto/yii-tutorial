<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
	<h4 class="modal-title">@yield( 'title' )</h4>
</div>
<div class="modal-body">
	@yield( 'body' )
</div>
<div class="modal-footer">
	<button type="button" class="btn dark btn-outline" data-dismiss="modal">Close</button>
	@yield( 'footer' )
</div>