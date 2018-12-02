@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Сегменты', route( 'segments.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.segments.create' ) )

        {!! Form::open( [ 'url' => route( 'segments.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">

            <div class="col-md-6">
                {!! Form::label( 'provider_id', 'Поставщик', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'provider_id', $providers, \Input::old( 'provider_id', $providers->count() == 1 ? $providers->keys()[ 0 ] : null ), [ 'class' => 'form-control select2', 'placeholder' => ' -- выберите из списка -- ', 'required' ] ) !!}
            </div>

            <div class="col-md-6">
                {!! Form::label( 'segment_type_id', 'Тип сегмента', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'segment_type_id', $segmentTypes, \Input::old( 'segment_type_id' ), [ 'class' => 'form-control select2', 'placeholder' => ' -- выберите из списка -- ', 'required' ] ) !!}
            </div>

        </div>

        <div class="form-group">

            <div class="col-md-6">
                {!! Form::label( 'parent_id', 'Родитель', [ 'class' => 'control-label' ] ) !!}
                <div id="parent_id" data-name="parent_id"></div>
            </div>

            <div class="col-md-6">
                {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Наименование', 'required' ] ) !!}
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

            .ready( function ( e )
            {

                $( '#parent_id' ).selectSegments();

            });

    </script>
@endsection