@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'Исполнители', route( 'managements.index' ) ],
        [ 'Добавить Исполнителя' ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="portlet light">
        <div class="portlet-body">
            <div class="tab-content">

                {!! Form::open( [ 'url' => route( 'managements.store' ) ] ) !!}

                <div class="form-group">
                    <label class="control-label">Наименование</label>
                    {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
                </div>

                <div class="form-group">
                    <label class="control-label">Адрес офиса</label>
                    {!! Form::text( 'address', \Input::old( 'address' ), [ 'class' => 'form-control', 'placeholder' => 'Адрес офиса' ] ) !!}
                </div>

                <div class="form-group">
                    <label class="control-label">Телефон приемной</label>
                    {!! Form::text( 'phone', \Input::old( 'phone' ), [ 'class' => 'form-control', 'placeholder' => 'Телефон приемной' ] ) !!}
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