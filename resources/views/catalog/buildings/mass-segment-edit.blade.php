@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Здания', route( 'buildings.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.buildings.edit' ) )

        <div class="panel panel-default">
            <div class="panel-body">

                {!! Form::model( $buildings, [ 'method' => 'get', 'route' => 'buildings.massUpdate', 'class' => 'form-horizontal submit-loading' ] ) !!}

                <input type="hidden" name="ids" value="{{ $ids }}">
                <input type="hidden" name="url_data" value="{{ $urlData }}">
                <input type="hidden" name="management_id" value="{{ $managementId }}">
                <div class="form-group">

                    <div class="col-md-1">
                        {!! Form::label( 'segment_id', 'Сегмент', [ 'class' => 'control-label' ] ) !!}
                    </div>
                    <div class="col-md-10">
                        <div id="segment_id" data-name="segment_id"></div>
                    </div>

                </div>

                <div class="form-group">
                    <div class="col-xs-6">
                        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                </div>

                {!! Form::close() !!}

            </div>
        </div>

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection

@section( 'js' )
    <script type="text/javascript">

        $(document)

            .ready(function () {

                $('#segment_id').selectSegments();

            });

    </script>
@endsection

