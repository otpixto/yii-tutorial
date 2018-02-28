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

    @if ( \Auth::user()->can( 'catalog.addresses.edit' ) )

        <div class="panel panel-default">
            <div class="panel-body">

                {!! Form::model( $address, [ 'method' => 'put', 'route' => [ 'addresses.update', $address->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                <div class="form-group">

                    <div class="col-xs-3">
                        {!! Form::label( 'region_id', 'Регион', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'region_id', $regions->pluck( 'name', 'id' ), \Input::old( 'region_id', $address->region_id ), [ 'class' => 'form-control select2', 'data-placeholder' => 'Регион' ] ) !!}
                    </div>

                    <div class="col-xs-9">
                        {!! Form::label( 'name', 'Адрес', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'name', \Input::old( 'name', $address->name ), [ 'class' => 'form-control', 'placeholder' => 'Адрес' ] ) !!}
                    </div>

                </div>

                <div class="form-group">

                    <div class="col-xs-12">
                        {!! Form::label( 'guid', 'GUID', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'guid', \Input::old( 'guid', $address->guid ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
                    </div>

                </div>

                <div class="form-group">
                    <div class="col-xs-12">
                        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                </div>

                {!! Form::close() !!}

            </div>

        </div>

        <ul class="nav nav-tabs">
            <li class="active">
                <a data-toggle="tab" href="#managements">
                    УО
                    <span class="badge" id="addresses-count">{{ $addressManagements->count() }}</span>
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div id="managements" class="tab-pane fade in active">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row margin-bottom-20">
                            <div class="col-xs-12">
                                <button type="button" id="add-managements" data-id="{{ $address->id }}" class="btn btn-default">
                                    <i class="glyphicon glyphicon-plus"></i>
                                    Добавить УО
                                </button>
                            </div>
                        </div>
                        @if ( ! $addressManagements->count() )
                            @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                        @endif
                        @foreach ( $addressManagements as $r )
                            <div class="margin-bottom-5">
                                <button type="button" class="btn btn-xs btn-danger" data-delete="address-management" data-address="{{ $address->id }}" data-management="{{ $r->id }}">
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

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

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

            })

            .on( 'click', '#add-managements', function ( e )
            {
                e.preventDefault();
                $.get( '{{ route( 'addresses.managements.add' ) }}', {
                    id: $( this ).attr( 'data-id' )
                }, function ( response )
                {
                    Modal.createSimple( 'Добавить УО', response, 'add-managements-modal' );
                });
            })

            .on( 'click', '#add-types', function ( e )
            {
                e.preventDefault();
                $.get( '{{ route( 'addresses.types.add' ) }}', {
                    id: $( this ).attr( 'data-id' )
                }, function ( response )
                {
                    Modal.createSimple( 'Добавить Классификатор', response, 'add-types-modal' );
                });
            })

            .on( 'click', '[data-delete="address-type"]', function ( e )
            {

                e.preventDefault();

                var address_id = $( this ).attr( 'data-address' );
                var type_id = $( this ).attr( 'data-type' );
                var obj = $( this ).closest( 'div' );

                bootbox.confirm({
                    message: 'Удалить привязку?',
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

                            obj.remove();

                            $.post( '{{ route( 'addresses.types.del' ) }}', {
                                address_id: address_id,
                                type_id: type_id
                            });

                        }
                    }
                });

            })

            .on( 'click', '[data-delete="address-management"]', function ( e )
            {

                e.preventDefault();

                var address_id = $( this ).attr( 'data-address' );
                var management_id = $( this ).attr( 'data-management' );
                var obj = $( this ).closest( 'div' );

                bootbox.confirm({
                    message: 'Удалить привязку?',
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

                            obj.remove();

                            $.post( '{{ route( 'addresses.managements.del' ) }}', {
                                address_id: address_id,
                                management_id: management_id
                            });

                        }
                    }
                });

            })

            .on( 'change', '#select-all-managements', function ()
            {
                if ( $( this ).is( ':checked' ) )
                {
                    $( '#management-add > option' ).prop( 'selected', 'selected' );
                    $( '#management-add' ).trigger( 'change' );
                }
                else
                {
                    $( '#management-add > option' ).removeAttr( 'selected' );
                    $( '#management-add' ).trigger( 'change' );
                }
            })

            .on( 'change', '#select-all-types', function ()
            {
                if ( $( this ).is( ':checked' ) )
                {
                    $( '#types-add > option' ).prop( 'selected', 'selected' );
                    $( '#types-add' ).trigger( 'change' );
                }
                else
                {
                    $( '#types-add > option' ).removeAttr( 'selected' );
                    $( '#types-add' ).trigger( 'change' );
                }
            });

    </script>
@endsection