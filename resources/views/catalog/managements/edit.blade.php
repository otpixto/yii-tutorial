@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'УК', route( 'managements.index' ) ],
        [ 'Редактировать "' . $management->name . '"' ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="portlet light">
        <div class="portlet-body">
            <div class="tab-content">

                {!! Form::model( $management, [ 'method' => 'put', 'route' => [ 'managements.update', $management->id ] ] ) !!}

                <div class="form-group">
                    <label class="control-label">Наименование</label>
                    {!! Form::text( 'name', \Input::old( 'name', $management->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
                </div>

                <div class="form-group">
                    <label class="control-label">Адрес офиса</label>
                    {!! Form::text( 'address', \Input::old( 'address', $management->address ), [ 'class' => 'form-control', 'placeholder' => 'Адрес офиса' ] ) !!}
                </div>

                <div class="form-group">
                    <label class="control-label">Телефон приемной</label>
                    {!! Form::text( 'phone', \Input::old( 'phone', $management->phone ), [ 'class' => 'form-control', 'placeholder' => 'Телефон приемной' ] ) !!}
                </div>

                <div class="margin-top-10">
                    {!! Form::submit( 'Редактировать', [ 'class' => 'btn green' ] ) !!}
                </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.min.css" rel="stylesheet" type="text/css" />
@endsection
