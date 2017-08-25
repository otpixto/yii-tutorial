<!-- BEGIN SEARCH -->
{!! Form::open( [ 'url' => route( 'tickets.index' ), 'method' => 'get', 'class' => 'search' ] ) !!}
{!! Form::text( 'search', null, [ 'class' => 'form-control', 'placeholder' => 'Поиск...' ] ) !!}
<button type="submit" class="btn submit md-skip">
    <i class="fa fa-search"></i>
</button>
{!! Form::close() !!}
<!-- END SEARCH -->