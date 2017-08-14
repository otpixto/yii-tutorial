@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'Здания', route( 'addresses.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="portlet light">
        <div class="portlet-body">
            <div class="tab-content">

                {!! Form::open( [ 'url' => route( 'addresses.store' ) ] ) !!}

                <div class="row">

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label">Наименование</label>
                            {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
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