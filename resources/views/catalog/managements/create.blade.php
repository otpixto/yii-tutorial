@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'Управляющие организации', route( 'managements.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'url' => route( 'managements.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

    <div class="form-group">
        <div class="col-xs-4">
            {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'phone', \Input::old( 'phone' ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'phone2', 'Доп. телефон', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'phone2', \Input::old( 'phone2' ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Доп. телефон' ] ) !!}
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-8">
            {!! Form::label( 'address', 'Адрес', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'address', \Input::old( 'address' ), [ 'class' => 'form-control', 'placeholder' => 'Адрес офиса' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'schedule', 'График работы', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'schedule', \Input::old( 'schedule' ), [ 'class' => 'form-control', 'placeholder' => 'График работы' ] ) !!}
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-4">
            {!! Form::label( 'director', 'ФИО руководителя', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'director', \Input::old( 'director' ), [ 'class' => 'form-control', 'placeholder' => 'ФИО руководителя' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'email', 'E-mail', [ 'class' => 'control-label' ] ) !!}
            {!! Form::email( 'email', \Input::old( 'email' ), [ 'class' => 'form-control', 'placeholder' => 'E-mail' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'site', 'Сайт', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'site', \Input::old( 'site' ), [ 'class' => 'form-control', 'placeholder' => 'Сайт' ] ) !!}
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-4">
            {!! Form::label( 'category_id', 'Категория ЭО', [ 'class' => 'control-label' ] ) !!}
            {!! Form::select( 'category_id', [ null => ' -- выберите из списка -- ' ] + \App\Models\Management::$categories, \Input::old( 'category_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Категория ЭО' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'services', 'Услуги', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'services', \Input::old( 'services' ), [ 'class' => 'form-control', 'placeholder' => 'Услуги' ] ) !!}
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
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )
            .ready(function()
            {

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

                $( '.select2' ).select2();

            });

    </script>
@endsection