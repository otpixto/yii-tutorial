@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'Типы обращений', route( 'types.index' ) ],
        [ 'Добавить тип' ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="portlet light">
        <div class="portlet-body">
            <div class="tab-content">

                {!! Form::open( [ 'url' => route( 'types.store' ) ] ) !!}

                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Наименование</label>
                            {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Категория обращений</label>
                            {!! Form::select( 'category_id', $categories, \Input::old( 'category_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Категория обращений' ] ) !!}
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Период на принятие заявки в работу, час</label>
                            {!! Form::number( 'period_acceptance', \Input::old( 'period_acceptance' ), [ 'class' => 'form-control', 'placeholder' => 'Период на принятие заявки в работу, час', 'step' => 0.1, 'min' => 0 ] ) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Период на исполнение, час</label>
                            {!! Form::number( 'period_execution', \Input::old( 'period_execution' ), [ 'class' => 'form-control', 'placeholder' => 'Период на исполнение, час', 'step' => 0.1, 'min' => 0 ] ) !!}
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="control-label">Сезонность устранения</label>
                            {!! Form::text( 'season', \Input::old( 'season' ), [ 'class' => 'form-control', 'placeholder' => 'Сезонность устранения' ] ) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">Необходим акт</label>
                            {!! Form::checkbox( 'need_act', 1, \Input::old( 'need_act' ), [ 'class' => 'form-control', 'placeholder' => 'Необходим акт' ] ) !!}
                        </div>
                    </div>

                </div>

                <div class="row margin-top-10">
                    <div class="col-md-12">
                        {!! Form::submit( 'Добавить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                </div>

                {!! Form::close() !!}

            </div>
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

            });

    </script>
@endsection