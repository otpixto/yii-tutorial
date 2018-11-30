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

        {!! Form::open( [ 'method' => 'post', 'url' => route( 'providers.phones.store', $provider->id ), 'class' => 'form-horizontal submit-loading' ] ) !!}
        {{ method_field( 'PUT' ) }}
        <div class="form-group">
            {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label col-md-4' ] ) !!}
            <div class="col-md-8">
                {!! Form::text( 'phone', null, [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label col-md-4' ] ) !!}
            <div class="col-md-8">
                {!! Form::text( 'name', null, [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label( 'description', 'Описание', [ 'class' => 'control-label col-md-4' ] ) !!}
            <div class="col-md-8">
                {!! Form::textarea( 'description', null, [ 'class' => 'form-control', 'placeholder' => 'Описание' ] ) !!}
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

@section( 'js' )
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {
                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });
            });

    </script>
@endsection