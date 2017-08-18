<!-- BEGIN SEARCH -->
{!! Form::open( [ 'url' => route( 'tickets.index' ), 'method' => 'get', 'class' => 'search' ] ) !!}
{!! Form::text( 'search', null, [ 'class' => 'form-control', 'placeholder' => 'Поиск...' ] ) !!}
<a href="javascript:;" class="btn submit md-skip">
    <i class="fa fa-search"></i>
</a>
{!! Form::close() !!}
<!-- END SEARCH -->