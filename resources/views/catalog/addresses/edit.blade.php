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

    {!! Form::model( $address, [ 'method' => 'put', 'route' => [ 'addresses.update', $address->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

    <div class="form-group">

        <div class="col-xs-9">
            {!! Form::label( 'address', 'Адрес', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'address', \Input::old( 'address', $address->address ), [ 'class' => 'form-control', 'placeholder' => 'Адрес' ] ) !!}
        </div>

        <div class="col-xs-3">
            {!! Form::label( 'home', 'Здание', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'home', \Input::old( 'home', $address->home ), [ 'class' => 'form-control', 'placeholder' => 'Здание' ] ) !!}
        </div>

    </div>

    <div class="form-group">
        <div class="col-xs-12">
            {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
        </div>
    </div>

    {!! Form::close() !!}

    <div class="row margin-top-15">
        <div class="col-md-12">
            <table class="table table-bordered table-hover table-striped">
                <thead>
                    <tr class="info">
                        <th width="50%">
                            ЭО
                        </th>
                        <th with="50%">
                            Типы обращений
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            @if ( ! $addressManagements->count() )
                                @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                            @endif
                            @foreach ( $addressManagements as $r )
                                <div class="margin-bottom-5">
                                    <a href="javascript:;" class="btn btn-xs btn-danger" data-delete="address-management" data-address="{{ $address->id }}" data-management="{{ $r->id }}">
                                        <i class="fa fa-remove"></i>
                                    </a>
                                    <a href="{{ route( 'managements.edit', $r->id ) }}">
                                        {{ $r->name }}
                                    </a>
                                </div>
                            @endforeach
                        </td>
                        <td>
                            @if ( ! $addressTypes->count() )
                                @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                            @endif
                            @foreach ( $addressTypes as $r )
                                <div class="margin-bottom-5">
                                    <a href="javascript:;" class="btn btn-xs btn-danger" data-delete="address-type" data-address="{{ $address->id }}" data-type="{{ $r->id }}">
                                        <i class="fa fa-remove"></i>
                                    </a>
                                    <a href="{{ route( 'types.edit', $r->id ) }}">
                                        {{ $r->name }}
                                    </a>
                                </div>
                            @endforeach
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td>
                            {!! Form::open( [ 'method' => 'post', 'url' => route( 'addresses.managements.add' ) ] ) !!}
                            {!! Form::hidden( 'address_id', $address->id ) !!}
                            <div class="row">
                                <div class="col-md-12">
                                    {!! Form::select( 'managements[]', $allowedManagements, null, [ 'class' => 'form-control select2', 'id' => 'management-add', 'multiple' ] ) !!}
                                </div>
                            </div>
                            <div class="row margin-top-10">
                                <div class="col-md-12">
                                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                        <input name="select_all_managements" id="select-all-managements" type="checkbox" value="1" />
                                        <span></span>
                                        Выбрать все
                                    </label>
                                    &nbsp;&nbsp;&nbsp;
                                    <button id="add-management" class="btn btn-success">
                                        <i class="glyphicon glyphicon-plus"></i>
                                        Добавить ЭО
                                    </button>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </td>
                        <td>
                            {!! Form::open( [ 'method' => 'post', 'url' => route( 'addresses.types.add' ) ] ) !!}
                            {!! Form::hidden( 'address_id', $address->id ) !!}
                            <div class="row">
                                <div class="col-md-12">
                                    {!! Form::select( 'types[]', $allowedTypes, null, [ 'class' => 'form-control select2', 'id' => 'types-add', 'multiple' ] ) !!}
                                </div>
                            </div>
                            <div class="row margin-top-10">
                                <div class="col-md-12 text-right">
                                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                        <input name="select_all_types" id="select-all-types" type="checkbox" value="1" />
                                        <span></span>
                                        Выбрать все
                                    </label>
                                    &nbsp;&nbsp;&nbsp;
                                    <button id="add-management" class="btn btn-success">
                                        <i class="glyphicon glyphicon-plus"></i>
                                        Добавить Тип
                                    </button>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                </tfoot>
            </table>

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