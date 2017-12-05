@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Регионы', route( 'regions.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row">

        <div class="col-lg-6">

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Редактрировать</h3>
                </div>
                <div class="panel-body">

                    {!! Form::model( $region, [ 'method' => 'put', 'route' => [ 'regions.update', $region->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                    <div class="form-group">

                        <div class="col-xs-6">
                            {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::text( 'name', \Input::old( 'name', $region->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
                        </div>
                        <div class="col-xs-6">
                            {!! Form::label( 'domain', 'Домен', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::text( 'domain', \Input::old( 'domain', $region->domain ), [ 'class' => 'form-control', 'placeholder' => 'Домен' ] ) !!}
                        </div>

                    </div>

                    <div class="form-group hidden-print">
                        <div class="col-xs-12">
                            {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                        </div>
                    </div>

                    {!! Form::close() !!}

                </div>

            </div>

        </div>

        <div class="col-lg-6">

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Телефоны</h3>
                </div>
                <div class="panel-body">
                    @foreach ( $region->phones as $phone )
                        <div class="row margin-top-5 margin-bottom-5">
                            <div class="col-xs-12">
                                <button type="button" class="btn btn-xs btn-danger" data-delete-phone="{{ $phone->id }}">
                                    <i class="fa fa-remove"></i>
                                </button>
                                {{ $phone->phone }}
                            </div>
                        </div>
                    @endforeach
                    {!! Form::open( [ 'url' => route( 'regions.phone.add', $region->id ), 'class' => 'form-horizontal submit-loading' ] ) !!}
                    <div class="form-group">
                        <div class="col-xs-12">
                            {!! Form::label( 'phone', 'Добавить телефон', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::text( 'phone', null, [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
                        </div>
                    </div>
                    <div class="form-group hidden-print">
                        <div class="col-xs-12">
                            {!! Form::submit( 'Добавить', [ 'class' => 'btn green' ] ) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>

        </div>

    </div>

    <ul class="nav nav-tabs">
        <li class="active">
            <a data-toggle="tab" href="#addresses">
                Здания
                <span class="badge" id="addresses-count">{{ $region->addresses->count() }}</span>
            </a>
        </li>
        <li>
            <a data-toggle="tab" href="#managements">
                УО
                <span class="badge" id="managements-count">{{ $region->managements->count() }}</span>
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="addresses" class="tab-pane fade in active">
            <div class="panel panel-default">
                <div class="panel-body">
                    @if ( ! $region->addresses->count() )
                        @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                    @endif
                    @foreach ( $region->addresses as $r )
                        <div class="margin-bottom-5">
                            <button type="button" class="btn btn-xs btn-danger">
                                <i class="fa fa-remove"></i>
                            </button>
                            <a href="{{ route( 'addresses.edit', $r->id ) }}">
                                {{ $r->getAddress() }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div id="managements" class="tab-pane fade">
            <div class="panel panel-default">
                <div class="panel-body">
                    @if ( ! $region->managements->count() )
                        @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                    @endif
                    @foreach ( $region->managements as $r )
                        <div class="margin-bottom-5">
                            <button type="button" class="btn btn-xs btn-danger">
                                <i class="fa fa-remove"></i>
                            </button>
                            <a href="{{ route( 'managements.edit', $r->id ) }}">
                                {{ $r->name }}
                            </a>
                        </div>
                    @endforeach
                </div>
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

            })

            .on( 'click', '[data-delete-phone]', function ( e )
            {

                var id = $( this ).attr( 'data-delete-phone' );
                var row = $( this ).closest( '.row' );

                bootbox.confirm({
                    message: 'Удалить телефон?',
                    size: 'small',
                    buttons: {
                        confirm: {
                            label: '<i class="fa fa-check"></i> Да',
                            className: 'btn-success'
                        },
                        cancel: {
                            label: '<i class="fa fa-times"></i> Нет',
                            className: 'btn-danger'
                        }
                    },
                    callback: function ( result )
                    {
                        if ( result )
                        {
                            $.post( '{{ route( 'regions.phone.del' ) }}', {
                                id: id
                            }, function ()
                            {
                                row.remove();
                            });
                        }
                    }

                });
            });

    </script>
@endsection