@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Поставщики', route( 'providers.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin )

        {!! Form::open( [ 'method' => 'post', 'url' => route( 'providers.keys.store', $provider->id ), 'class' => 'form-horizontal submit-loading' ] ) !!}
        {{ method_field( 'PUT' ) }}
        <div class="form-group">
            {!! Form::label( 'token_life', 'Время жизни токена (минут)', [ 'class' => 'control-label col-md-4' ] ) !!}
            <div class="col-md-8">
                {!! Form::number( 'token_life', 60, [ 'class' => 'form-control', 'step' => 1, 'min' => 1 ] ) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label( 'description', 'Описание', [ 'class' => 'control-label col-md-4' ] ) !!}
            <div class="col-md-8">
                {!! Form::textarea( 'description', null, [ 'class' => 'form-control', 'placeholder' => 'Описание' ] ) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label( 'ip', 'IP', [ 'class' => 'control-label col-md-4' ] ) !!}
            <div class="col-md-8">
                {!! Form::textarea( 'ip', null, [ 'class' => 'form-control', 'placeholder' => 'IP' ] ) !!}
            </div>
        </div>
        <div class="form-group hidden-print">
            <div class="col-md-8 col-md-offset-4">
                {!! Form::submit( 'Добавить', [ 'class' => 'btn green' ] ) !!}
            </div>
        </div>
        {!! Form::close() !!}

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection