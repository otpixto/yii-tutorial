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
                        <label class="control-label">Телефон-1</label>
                        {!! Form::text( 'phone1', \Input::old( 'phone1' ), [ 'class' => 'form-control', 'placeholder' => 'Телефон-1', 'required' ] ) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">Телефон-2</label>
                        {!! Form::text( 'phone2', \Input::old( 'phone2' ), [ 'class' => 'form-control', 'placeholder' => 'Телефон-2' ] ) !!}
                    </div>
                </div>

            </div>

            <div class="row">

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">Фамилия</label>
                        {!! Form::text( 'lastname', \Input::old( 'lastname' ), [ 'class' => 'form-control', 'placeholder' => 'Фамилия' ] ) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">Имя</label>
                        {!! Form::text( 'firstname', \Input::old( 'firstname' ), [ 'class' => 'form-control', 'placeholder' => 'Имя', 'required' ] ) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">Отчество</label>
                        {!! Form::text( 'middlename', \Input::old( 'middlename' ), [ 'class' => 'form-control', 'placeholder' => 'Отчество' ] ) !!}
                    </div>
                </div>

            </div>

            <hr />

            <div class="row">

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">Тип обращения</label>
                        {!! Form::select( 'type_id', $types, \Input::old( 'type_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Тип обращения', 'required' ] ) !!}
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="form-group">
                        <label class="control-label">Адрес</label>
                        {!! Form::text( 'address', \Input::old( 'address' ), [ 'class' => 'form-control', 'placeholder' => 'Адрес', 'required' ] ) !!}
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
                                    <label class="control-label">Наименование УК</label>
                                    <span class="form-control" id="management_name">
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Телефон УК</label>
                                    <span class="form-control" id="management_phone">
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">Адрес УК</label>
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
                        <label class="control-label">Текст обращения</label>
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
@endsection

@section( 'js' )



@endsection