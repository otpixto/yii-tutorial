@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'Заявители', route( 'customers.index' ) ],
        [ 'Добавить заявителя' ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="portlet light">
        <div class="portlet-body">
            <div class="tab-content">

                {!! Form::open( [ 'url' => route( 'customers.store' ) ] ) !!}

                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label( 'lastname', 'Фамилия', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::text( 'lastname', \Input::old( 'lastname' ), [ 'class' => 'form-control', 'placeholder' => 'Фамилия' ] ) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label( 'firstname', 'Имя', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::text( 'firstname', \Input::old( 'firstname' ), [ 'class' => 'form-control', 'placeholder' => 'Имя', 'required' ] ) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label( 'middlename', 'Отчество', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::text( 'middlename', \Input::old( 'middlename' ), [ 'class' => 'form-control', 'placeholder' => 'Отчество' ] ) !!}
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::text( 'phone', \Input::old( 'phone', \Input::get( 'phone' ) ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон', 'required' ] ) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label( 'phone2', 'Доп. телефон', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::text( 'phone2', \Input::old( 'phone2' ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Доп. телефон' ] ) !!}
                        </div>
                    </div>

                </div>

                <div class="margin-top-10">
                    {!! Form::submit( 'Добавить', [ 'class' => 'btn green' ] ) !!}
                </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.min.css" rel="stylesheet" type="text/css" />
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