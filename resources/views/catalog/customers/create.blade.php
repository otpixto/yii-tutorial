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

    <div class="portlet light">
        <div class="portlet-body">
            <div class="tab-content">

                {!! Form::open( [ 'url' => route( 'customers.store' ), 'class' => 'form-horizontal' ] ) !!}

                <div class="form-group">

                    <div class="col-xs-4">
                        {!! Form::label( 'lastname', 'Фамилия', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'lastname', \Input::old( 'lastname' ), [ 'class' => 'form-control', 'placeholder' => 'Фамилия' ] ) !!}
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

                </div>

                <div class="form-group">

                    <div class="col-xs-4">
                        {!! Form::label( 'actual_address_id', 'Адрес проживания', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'actual_address_id', \Input::old( 'actual_address_id' ) ? \App\Models\Address::find( \Input::old( 'actual_address_id' ) )->pluck( 'name', 'id' ) : [], \Input::old( 'actual_address_id' ), [ 'class' => 'form-control select2-ajax', 'placeholder' => 'Адрес проживания', 'data-ajax--url' => route( 'addresses.search' ), 'data-ajax--cache' => true, 'data-placeholder' => 'Адрес проживания', 'data-allow-clear' => true, 'required' ] ) !!}
                    </div>

                    <div class="col-xs-4">
                        {!! Form::label( 'flat', 'Квартира', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'flat', \Input::old( 'flat' ), [ 'class' => 'form-control', 'placeholder' => 'Квартира' ] ) !!}
                    </div>

                </div>

                <div class="form-group">
                    <div class="col-xs-12">
                        {!! Form::submit( 'Добавить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

                $( '.select2' ).select2();

                $( '.select2-ajax' ).select2({
                    minimumInputLength: 3,
                    minimumResultsForSearch: 30,
                    ajax: {
                        delay: 450,
                        processResults: function ( data, page )
                        {
                            return {
                                results: data
                            };
                        }
                    }
                });

            });

    </script>
@endsection