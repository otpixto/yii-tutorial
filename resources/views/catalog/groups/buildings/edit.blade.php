@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Группы', route( 'buildings_groups.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.groups.edit' ) )

        {!! Form::model( $group, [ 'method' => 'put', 'route' => [ 'buildings_groups.update', $group->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">

            <div class="col-md-12">
                {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'name', \Input::old( 'name', $group->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование', 'required' ] ) !!}
            </div>

        </div>

        <div class="form-group">
            <div class="col-xs-6">
                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
            </div>
            <div class="col-xs-6 text-right">
                <a href="{{ route( 'buildings_groups.buildings', $group->id ) }}" class="btn btn-default btn-circle">
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