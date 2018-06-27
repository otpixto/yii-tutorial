@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'Здания', route( 'addresses.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.addresses.create' ) )

        {!! Form::open( [ 'url' => route( 'addresses.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">

            <div class="col-xs-8">
                {!! Form::label( 'name', 'Адрес', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Адрес' ] ) !!}
            </div>

            <div class="col-xs-4">
                {!! Form::label( 'guid', 'GUID', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'guid', \Input::old( 'guid' ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
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