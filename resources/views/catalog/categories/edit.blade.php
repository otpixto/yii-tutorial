@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Категории классификатора', route( 'categories.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.categories.edit' ) )

        {!! Form::model( $category, [ 'method' => 'put', 'route' => [ 'categories.update', $category->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">
            <label class="control-label">Наименование</label>
            {!! Form::text( 'name', \Input::old( 'name', $category->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
        </div>

        <div class="margin-top-10">
            {!! Form::submit( 'Редактировать', [ 'class' => 'btn green' ] ) !!}
        </div>

        {!! Form::close() !!}

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection