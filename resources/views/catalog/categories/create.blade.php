@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'Категории обращений', route( 'categories.index' ) ],
        [ 'Добавить категорию' ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'url' => route( 'categories.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

    <div class="form-group">
        <div class="col-xs-12">
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

@endsection

@section( 'css' )
@endsection