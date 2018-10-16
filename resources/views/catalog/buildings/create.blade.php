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

            <div class="col-md-6">
                {!! Form::label( 'provider_id', 'Поставщик', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'provider_id', $providers, \Input::old( 'provider_id' ), [ 'class' => 'form-control select2', 'placeholder' => ' -- выберите из списка -- ', 'required' ] ) !!}
            </div>

            <div class="col-md-6">
                {!! Form::label( 'guid', 'GUID', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'guid', \Input::old( 'guid' ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
            </div>

        </div>

        <div class="form-group">

            <div class="col-md-4">
                {!! Form::label( 'building_type_id', 'Тип здания', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'building_type_id', $buildingTypes, \Input::old( 'building_type_id' ), [ 'class' => 'form-control select2', 'placeholder' => ' -- выберите из списка -- ', 'required' ] ) !!}
            </div>

            <div class="col-md-6">
                {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Наименование', 'required' ] ) !!}
            </div>

            <div class="col-md-2">
                {!! Form::label( 'number', 'Номер', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'number', \Input::old( 'number' ), [ 'class' => 'form-control', 'placeholder' => 'Номер' ] ) !!}
            </div>

        </div>

        <div class="form-group">

            <div class="col-md-12">
                {!! Form::label( 'segment_id', 'Сегмент', [ 'class' => 'control-label' ] ) !!}
                <div id="segment_id" data-name="segment_id"></div>
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

                $( '#segment_id' ).selectSegments();

            });

    </script>
@endsection