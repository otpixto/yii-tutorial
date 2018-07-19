@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Поставщики', route( 'providers.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.providers.create' ) )

        {!! Form::open( [ 'url' => route( 'providers.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">

            <div class="col-xs-6">
                {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
            </div>

            <div class="col-xs-6">
                {!! Form::label( 'domain', 'Домен', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'domain', \Input::old( 'domain' ), [ 'class' => 'form-control', 'placeholder' => 'Домен' ] ) !!}
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