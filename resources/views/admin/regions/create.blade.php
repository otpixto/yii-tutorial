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

    {!! Form::open( [ 'url' => route( 'regions.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

    <div class="form-group">

        <div class="col-xs-6">
            {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
        </div>

        <div class="col-xs-6">
            {!! Form::label( 'domain', 'Домен', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'domain', \Input::old( 'domain' ), [ 'class' => 'form-control', 'placeholder' => 'Домен' ] ) !!}
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