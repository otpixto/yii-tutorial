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

        {!! Form::open([ 'method' => 'post', 'url' => route( 'providers.phones.update', [ $provider->id, $phone->id ] ), 'class' => 'form-horizontal submit-loading' ] ) !!}
        <div class="form-group">
            {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label col-md-4' ] ) !!}
            <div class="col-md-8">
                {!! Form::text( 'phone', $phone->phone, [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label col-md-4' ] ) !!}
            <div class="col-md-8">
                {!! Form::text( 'name', $phone->name, [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label( 'description', 'Описание', [ 'class' => 'control-label col-md-4' ] ) !!}
            <div class="col-md-8">
                {!! Form::textarea( 'description', $phone->description, [ 'class' => 'form-control', 'placeholder' => 'Описание' ] ) !!}
            </div>
        </div>
        <div class="form-group hidden-print">
            <div class="col-md-8 col-md-offset-4">
                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
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