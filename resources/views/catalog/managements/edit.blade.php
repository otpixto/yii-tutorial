@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'Управляющие организации', route( 'managements.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::model( $management, [ 'method' => 'put', 'route' => [ 'managements.update', $management->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

    <div class="form-group">
        <div class="col-xs-4">
            {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'name', \Input::old( 'name', $management->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'phone', \Input::old( 'phone', $management->phone ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'phone2', 'Доп. телефон', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'phone2', \Input::old( 'phone2', $management->phone2 ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Доп. телефон' ] ) !!}
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-8">
            {!! Form::label( 'address', 'Адрес', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'address', \Input::old( 'address', $management->address ), [ 'class' => 'form-control', 'placeholder' => 'Адрес офиса' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'schedule', 'График работы', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'schedule', \Input::old( 'schedule', $management->schedule ), [ 'class' => 'form-control', 'placeholder' => 'График работы' ] ) !!}
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-4">
            {!! Form::label( 'director', 'ФИО руководителя', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'director', \Input::old( 'director', $management->director ), [ 'class' => 'form-control', 'placeholder' => 'ФИО руководителя' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'email', 'E-mail', [ 'class' => 'control-label' ] ) !!}
            {!! Form::email( 'email', \Input::old( 'email', $management->email ), [ 'class' => 'form-control', 'placeholder' => 'E-mail' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'site', 'Сайт', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'site', \Input::old( 'site', $management->site ), [ 'class' => 'form-control', 'placeholder' => 'Сайт' ] ) !!}
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-4">
            {!! Form::label( 'category_id', 'Категория ЭО', [ 'class' => 'control-label' ] ) !!}
            {!! Form::select( 'category_id', \App\Models\Management::$categories, \Input::old( 'category_id', $management->category_id ), [ 'class' => 'form-control select2', 'placeholder' => 'Категория ЭО' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'services', 'Услуги', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'services', \Input::old( 'services', $management->services ), [ 'class' => 'form-control', 'placeholder' => 'Услуги' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'has_contract', 'Заключен договор', [ 'class' => 'control-label' ] ) !!}
            {!! Form::select( 'has_contract', [ 0 => 'Нет', 1 => 'Да' ], \Input::old( 'has_contract', $management->has_contract ), [ 'class' => 'form-control', 'placeholder' => 'Заключен договор' ] ) !!}
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-12">
            {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
        </div>
    </div>

    {!! Form::close() !!}

    <h3>Оповещения в Telegram</h3>

    {!! Form::open( [ 'url' => route( 'managements.telegram' ), 'class' => 'form-horizontal submit-loading' ] ) !!}
    <div class="form-group">
        @if ( ! $management->telegram_code )
            <div class="col-xs-12">
                <button type="button" class="btn btn-success" data-action="telegram-on" data-id="{{ $management->id }}">Подключить</button>
            </div>
        @else
            <div class="col-xs-6">
                <button type="button" class="btn btn-danger" data-action="telegram-off" data-id="{{ $management->id }}">Отключить</button>
                <button type="button" class="btn btn-warning" data-action="telegram-gen" data-id="{{ $management->id }}">Сгенерировать пин-код</button>
            </div>
            <label class="col-xs-3 control-label">
                Пин-код
            </label>
            <div class="col-xs-3">
                <span class="form-control">
                    {{ $management->telegram_code }}
                </span>
            </div>
        @endif
    </div>
    {!! Form::close() !!}

    <div class="row margin-top-15">
        <div class="col-md-12">
            <table class="table table-bordered table-hover table-striped">
                <thead>
                <tr class="info">
                    <th width="50%">
                        Адреса
                    </th>
                    <th with="50%">
                        Типы обращений
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        @if ( ! $managementAddresses->count() )
                            @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                        @endif
                        @foreach ( $managementAddresses as $r )
                            <div class="margin-bottom-5">
                                <a href="javascript:;" class="btn btn-xs btn-danger" data-delete="management-address" data-management="{{ $management->id }}" data-address="{{ $r->id }}">
                                    <i class="fa fa-remove"></i>
                                </a>
                                <a href="{{ route( 'addresses.edit', $r->id ) }}">
                                    {{ $r->name }}
                                </a>
                            </div>
                        @endforeach
                    </td>
                    <td>
                        @if ( ! $managementTypes->count() )
                            @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                        @endif
                        @foreach ( $managementTypes as $r )
                            <div class="margin-bottom-5">
                                <a href="javascript:;" class="btn btn-xs btn-danger" data-delete="management-type" data-management="{{ $management->id }}" data-type="{{ $r->id }}">
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
                        {!! Form::open( [ 'method' => 'post', 'url' => route( 'managements.addresses.add' ) ] ) !!}
                        {!! Form::hidden( 'management_id', $management->id ) !!}
                        <div class="row">
                            <div class="col-md-12">
                                {!! Form::select( 'addresses[]', $allowedAddresses, null, [ 'class' => 'form-control select2', 'id' => 'addresses-add', 'multiple' ] ) !!}
                            </div>
                        </div>
                        <div class="row margin-top-10">
                            <div class="col-md-12">
                                <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                    <input name="select_all_addresses" id="select-all-addresses" type="checkbox" value="1" />
                                    <span></span>
                                    Выбрать все
                                </label>
                                &nbsp;&nbsp;&nbsp;
                                <button id="add-management" class="btn btn-success">
                                    <i class="glyphicon glyphicon-plus"></i>
                                    Добавить Адрес
                                </button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </td>
                    <td>
                        {!! Form::open( [ 'method' => 'post', 'url' => route( 'managements.types.add' ) ] ) !!}
                        {!! Form::hidden( 'management_id', $management->id ) !!}
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
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready(function()
            {

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

                $( '.select2' ).select2();

            })

            .on( 'click', '[data-delete="management-address"]', function ( e )
            {

                e.preventDefault();

                var management_id = $( this ).attr( 'data-management' );
                var address_id = $( this ).attr( 'data-address' );
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

                            $.post( '{{ route( 'managements.addresses.del' ) }}', {
                                management_id: management_id,
                                address_id: address_id
                            });

                        }
                    }
                });

            })

            .on( 'click', '[data-delete="management-type"]', function ( e )
            {

                e.preventDefault();

                var management_id = $( this ).attr( 'data-management' );
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

                            $.post( '{{ route( 'managements.types.del' ) }}', {
                                management_id: management_id,
                                type_id: type_id
                            });

                        }
                    }
                });

            })

            .on( 'click', '[data-action="telegram-on"]', function ( e )
            {

                e.preventDefault();

                var id = $( this ).attr( 'data-id' );

                bootbox.confirm({
                    message: 'Включить оповещения?',
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

                            $.post( '{{ route( 'managements.telegram' ) }}', {
                                id: id,
                                action: 'on'
                            }, function ( response )
                            {
                                window.location.reload();
                            });

                        }
                    }
                });

            })

            .on( 'click', '[data-action="telegram-off"]', function ( e ) {

                e.preventDefault();

                var id = $(this).attr('data-id');

                bootbox.confirm({
                    message: 'Отключить оповещения?',
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
                    callback: function (result) {
                        if (result) {

                            $.post('{{ route( 'managements.telegram' ) }}', {
                                id: id,
                                action: 'off'
                            }, function (response) {
                                window.location.reload();
                            });

                        }
                    }
                });

            })

            .on( 'click', '[data-action="telegram-gen"]', function ( e ) {

                e.preventDefault();

                var id = $(this).attr('data-id');

                bootbox.confirm({
                    message: 'Сгенерировать новый пин-код?',
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
                    callback: function (result) {
                        if (result) {

                            $.post('{{ route( 'managements.telegram' ) }}', {
                                id: id,
                                action: 'gen'
                            }, function (response) {
                                window.location.reload();
                            });

                        }
                    }
                });

            })

            .on( 'change', '#select-all-addresses', function ()
            {
                if ( $( this ).is( ':checked' ) )
                {
                    $( '#addresses-add > option' ).prop( 'selected', 'selected' );
                    $( '#addresses-add' ).trigger( 'change' );
                }
                else
                {
                    $( '#addresses-add > option' ).removeAttr( 'selected' );
                    $( '#addresses-add' ).trigger( 'change' );
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