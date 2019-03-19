@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Группы', route( 'groups.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.groups.edit' ) )

        {!! Form::model( $group, [ 'method' => 'put', 'route' => [ 'groups.update', $group->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">

            <div class="col-md-4">
                {!! Form::label( 'provider_id', 'Поставщик', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'provider_id', $providers, \Input::old( 'provider_id', $group->provider_id ), [ 'class' => 'form-control select2', 'placeholder' => ' -- выберите из списка -- ', 'required' ] ) !!}
            </div>

            <div class="col-md-8">
                {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'name', \Input::old( 'name', $group->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование', 'required' ] ) !!}
            </div>

        </div>

        <div class="form-group">
            <div class="col-xs-6">
                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
            </div>
            <div class="col-xs-6 text-right">
                <a href="{{ route( 'groups.buildings', $group->id ) }}" class="btn btn-default btn-circle">
                    Адреса
                    <span class="badge">
                        {{ $group->buildings()->count() }}
                    </span>
                </a>
            </div>
        </div>

        {!! Form::close() !!}

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection