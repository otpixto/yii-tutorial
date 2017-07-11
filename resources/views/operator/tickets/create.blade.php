@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Обращения', route( 'tickets.index' ) ],
        [ 'Добавить обращение' ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="portlet light ">
        <div class="portlet-body">

            <h1 class="margin-top-10 margin-bottom-30">
                <i class="fa fa-plus-square text-success"></i>
                Регистрация обращения
            </h1>

            {!! Form::open( [ 'url' => route( 'tickets.store' ) ] ) !!}

            <div class="row">

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label( 'phone1', 'Телефон-1', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'phone1', \Input::old( 'phone1' ), [ 'class' => 'form-control', 'placeholder' => 'Телефон-1', 'required' ] ) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label( 'phone2', 'Телефон-2', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'phone2', \Input::old( 'phone2' ), [ 'class' => 'form-control', 'placeholder' => 'Телефон-2' ] ) !!}
                    </div>
                </div>

            </div>

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

            <hr />

            <div class="row">

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label( 'type_id', 'Тип обращения', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'type_id', $types, \Input::old( 'type_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Тип обращения', 'required' ] ) !!}
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::label( 'address', 'Адрес', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'address', \Input::old( 'address' ), [ 'class' => 'form-control', 'placeholder' => 'Адрес', 'required', 'id' => 'address' ] ) !!}
                    </div>
                </div>

            </div>

            <div class="row">

                <div class="col-md-12">

                    <div class="alert alert-info">

                        <div class="row">

                            {!! Form::hidden( 'management_id', null, [ 'id' => 'management_id' ] ) !!}

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label( null, 'Наименование УК', [ 'class' => 'control-label' ] ) !!}
                                    <span class="form-control" id="management_name">
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label( null, 'Телефон УК', [ 'class' => 'control-label' ] ) !!}
                                    <span class="form-control" id="management_phone">
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    {!! Form::label( null, 'Адрес УК', [ 'class' => 'control-label' ] ) !!}
                                    <span class="form-control" id="management_address">
                                    </span>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <hr />

            <div class="row">

                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label( 'text', 'Текст обращения', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::textarea( 'text', \Input::old( 'text' ), [ 'class' => 'form-control', 'placeholder' => 'Текст обращения', 'required' ] ) !!}
                    </div>
                </div>

            </div>

            <div class="row margiv-top-10">
                <div class="col-md-12">
                    {!! Form::submit( 'Добавить', [ 'class' => 'btn green' ] ) !!}
                </div>
            </div>

            {!! Form::close() !!}

        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )
            .ready(function()
            {

                $( '.select2' ).select2();

                //$( '#address' ).

            });

    </script>
@endsection