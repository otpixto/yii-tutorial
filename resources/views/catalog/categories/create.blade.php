@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Категории классификатора', route( 'categories.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.categories.create' ) )

        {!! Form::open( [ 'url' => route( 'categories.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">
            <div class="col-md-4">
                {!! Form::label( 'provider_id', 'Поставщик', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'provider_id', $providers, \Input::old( 'provider_id', $providers->count() == 1 ? $providers->keys()[ 0 ] : null ), [ 'class' => 'form-control select2', 'placeholder' => ' -- выберите из списка -- ', 'required' ] ) !!}
            </div>
            <div class="col-md-8">
                {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
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