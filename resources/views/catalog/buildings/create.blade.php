@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Здания', route( 'buildings.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.buildings.create' ) )

        {!! Form::open( [ 'url' => route( 'buildings.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">

            <div class="col-md-4">
                {!! Form::label( 'provider_id', 'Поставщик', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'provider_id', $providers, \Input::old( 'provider_id' ), [ 'class' => 'form-control select2', 'placeholder' => ' -- выберите из списка -- ' ] ) !!}
            </div>

            <div class="col-xs-6">
                {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
            </div>

            <div class="col-xs-2">
                {!! Form::label( 'guid', 'GUID', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'guid', \Input::old( 'guid' ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
            </div>

        </div>

        <div class="form-group">

            <div class="col-md-12">
                {!! Form::label( 'segment_id', 'Сегмент', [ 'class' => 'control-label' ] ) !!}
                <span id="segment" class="form-control text-muted">
                    Нажмите, чтобы выбрать
                </span>
                {!! Form::hidden( 'segment_id', \Input::old( 'segment_id' ) ) !!}
            </div>

        </div>

        <div class="form-group">
            <div class="col-xs-12">
                {!! Form::submit( 'Добавить', [ 'class' => 'btn green' ] ) !!}
            </div>
        </div>

        {!! Form::close() !!}

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection

@section( 'js' )
    <script src="/assets/global/plugins/bootstrap-treeview.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .on( 'click', '#segment', function ( e )
            {

                e.preventDefault();

                Modal.create( 'segment-modal', function ()
                {
                    Modal.setTitle( 'Выберите сегмент' );
                    $.get( '{{ route( 'segments.tree' ) }}', function ( response )
                    {
                        var tree = $( '<div></div>' ).attr( 'id', 'segment-tree' );
                        Modal.setBody( tree );
                        tree.treeview({
                            data: response,
                            onNodeSelected: function ( event, node )
                            {
                                $( '#segment_id' ).val( node.id );
                                $( '#segment' ).text( node.text ).removeClass( 'text-muted' );
                            },
                            onNodeUnselected: function ( event, node )
                            {
                                $( '#segment_id' ).val( '' );
                                $( '#segment' ).text( 'Нажмите, чтобы выбрать' ).addClass( 'text-muted' );
                            }
                        });
                    });
                });

            });

    </script>
@endsection