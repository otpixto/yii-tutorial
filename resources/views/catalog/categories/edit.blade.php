@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Классификатор', route( 'types.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.types.edit_category' ) )

        {!! Form::model( $category, [ 'method' => 'put', 'route' => [ 'categories.update', $category->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">
            <div class="col-xs-12">
                {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'name', \Input::old( 'name', $category->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-12">
                {!! Form::submit( 'Редактировать', [ 'class' => 'btn green' ] ) !!}
            </div>
        </div>

        {!! Form::close() !!}

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection