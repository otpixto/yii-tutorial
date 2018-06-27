@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Роли', route( 'roles.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.roles.create' ) )

        {!! Form::open( [ 'url' => route( 'roles.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">
            <div class="col-xs-12">
                {!! Form::label( 'code', 'Код', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'code', \Input::old( 'code' ), [ 'class' => 'form-control', 'placeholder' => 'Код', 'required' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-12">
                {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Наименование', 'required' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-12">
                {!! Form::label( 'guard', 'Guard', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'guard', $guards, \Input::old( 'guard', config( 'defaults.guard' ) ), [ 'class' => 'form-control', 'required' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-12">
                {!! Form::submit( 'Создать', [ 'class' => 'btn green' ] ) !!}
            </div>
        </div>

        {!! Form::close() !!}

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection