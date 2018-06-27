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

    @if ( \Auth::user()->can( 'catalog.addresses.edit' ) )

        <div class="panel panel-default">
            <div class="panel-body">

                {!! Form::model( $address, [ 'method' => 'put', 'route' => [ 'addresses.update', $address->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                <div class="form-group">

                    <div class="col-xs-8">
                        {!! Form::label( 'name', 'Адрес', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'name', \Input::old( 'name', $address->name ), [ 'class' => 'form-control', 'placeholder' => 'Адрес' ] ) !!}
                    </div>

                    <div class="col-xs-4">
                        {!! Form::label( 'guid', 'GUID', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'guid', \Input::old( 'guid', $address->guid ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
                    </div>

                </div>

                <div class="form-group">
                    <div class="col-xs-6">
                        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                    <div class="col-xs-6 text-right">
                        <a href="{{ route( 'addresses.regions', $address->id ) }}" class="btn btn-default btn-circle">
                            Регионы
                            <span class="badge">{{ $addressRegionsCount }}</span>
                        </a>
                        <a href="{{ route( 'addresses.managements', $address->id ) }}" class="btn btn-default btn-circle">
                            УО
                            <span class="badge">{{ $addressManagementsCount }}</span>
                        </a>
                    </div>

                </div>

                {!! Form::close() !!}

            </div>

        </div>

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection