@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'Заявители', route( 'customers.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.customers.create' ) )

        {!! Form::open( [ 'url' => route( 'customers.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">

            <div class="col-xs-3">
                {!! Form::label( 'region_id', 'Регион', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'region_id', $regions->pluck( 'name', 'id' ), \Input::old( 'region_id' ), [ 'class' => 'form-control select2', 'data-placeholder' => 'Регион' ] ) !!}
            </div>

            <div class="col-xs-7">
                {!! Form::label( 'actual_address_id', 'Адрес проживания', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'actual_address_id', [], \Input::old( 'actual_address_id' ), [ 'class' => 'form-control select2-ajax', 'placeholder' => 'Адрес проживания', 'data-ajax--url' => route( 'addresses.search' ), 'data-placeholder' => 'Адрес проживания', 'required' ] ) !!}
            </div>

            <div class="col-xs-2">
                {!! Form::label( 'actual_flat', 'Квартира', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'actual_flat', \Input::old( 'actual_flat' ), [ 'class' => 'form-control', 'placeholder' => 'Квартира' ] ) !!}
            </div>

        </div>

        <div class="form-group">

            <div class="col-xs-4">
                {!! Form::label( 'lastname', 'Фамилия', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'lastname', \Input::old( 'lastname' ), [ 'class' => 'form-control', 'placeholder' => 'Фамилия', 'required' ] ) !!}
            </div>

            <div class="col-xs-4">
                {!! Form::label( 'firstname', 'Имя', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'firstname', \Input::old( 'firstname' ), [ 'class' => 'form-control', 'placeholder' => 'Имя', 'required' ] ) !!}
            </div>

            <div class="col-xs-4">
                {!! Form::label( 'middlename', 'Отчество', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'middlename', \Input::old( 'middlename' ), [ 'class' => 'form-control', 'placeholder' => 'Отчество' ] ) !!}
            </div>

        </div>

        <div class="form-group">

            <div class="col-xs-4">
                {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'phone', \Input::old( 'phone' ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон', 'required' ] ) !!}
            </div>

            <div class="col-xs-4">
                {!! Form::label( 'phone2', 'Доп. телефон', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'phone2', \Input::old( 'phone2' ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Доп. телефон' ] ) !!}
            </div>

            <div class="col-xs-4">
                {!! Form::label( 'email', 'E-mail', [ 'class' => 'control-label' ] ) !!}
                {!! Form::email( 'email', \Input::old( 'email' ), [ 'class' => 'form-control', 'placeholder' => 'E-mail' ] ) !!}
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