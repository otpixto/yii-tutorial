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

    @if ( \Auth::user()->can( 'catalog.managements.edit' ) )

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Редактрировать</h3>
            </div>
            <div class="panel-body">

                {!! Form::model( $management, [ 'method' => 'put', 'route' => [ 'managements.update', $management->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                <div class="form-group">

                    <div class="col-xs-3">
                        {!! Form::label( 'region_id', 'Регион', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'region_id', $regions->pluck( 'name', 'id' ), \Input::old( 'region_id', $management->region_id ), [ 'class' => 'form-control select2', 'data-placeholder' => 'Регион' ] ) !!}
                    </div>

                    <div class="col-xs-4">
                        {!! Form::label( 'address_id', 'Адрес', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'address_id', $management->addressRelation ? $management->addressRelation->pluck( 'name', 'id' ) : [], \Input::old( 'address_id', $management->address_id ), [ 'class' => 'form-control', 'placeholder' => 'Адрес офиса', 'data-ajax--url' => route( 'addresses.search' ), 'data-ajax--cache' => true, 'data-placeholder' => 'Адрес офиса', 'data-allow-clear' => true ] ) !!}
                    </div>

                    <div class="col-xs-5">
                        {!! Form::label( 'address', 'Адрес', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'address', \Input::old( 'address', $management->address ), [ 'class' => 'form-control', 'placeholder' => 'Адрес офиса' ] ) !!}
                    </div>

                </div>

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
                        {!! Form::label( 'schedule', 'График работы', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'schedule', \Input::old( 'schedule', $management->schedule ), [ 'class' => 'form-control', 'placeholder' => 'График работы' ] ) !!}
                    </div>

                </div>

                <h3>Договор</h3>

                <div class="form-group">
                    <div class="col-xs-4">
                        {!! Form::label( 'has_contract', 'Заключен договор', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'has_contract', [ 0 => 'Нет', 1 => 'Да' ], \Input::old( 'has_contract', $management->has_contract ), [ 'class' => 'form-control', 'placeholder' => 'Заключен договор' ] ) !!}
                    </div>
                    <div class="col-xs-4">
                        {!! Form::label( 'contract_number', 'Номер договора', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'contract_number', $management->contract_number, [ 'class' => 'form-control', 'placeholder' => 'Номер договора' ] ) !!}
                    </div>
                    <div class="col-xs-4">
                        {!! Form::label( 'contract_begin', 'Действие договора', [ 'class' => 'control-label' ] ) !!}
                        <div class="input-group">
                            {!! Form::text( 'contract_begin', $management->contract_begin, [ 'class' => 'form-control datepicker', 'placeholder' => 'ОТ' ] ) !!}
                            <span class="input-group-addon">-</span>
                            {!! Form::text( 'contract_end', $management->contract_end, [ 'class' => 'form-control datepicker', 'placeholder' => 'ДО' ] ) !!}
                        </div>
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

        <div class="row">

            <div class="col-lg-6">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            Оповещения в Telegram
                        </h3>
                    </div>
                    <div class="panel-body">

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

                        @if ( $management->telegram_code )
                            <h3>
                                Подписки
                                ({{ $management->subscriptions->count() }})
                            </h3>
                            <ul class="list-group">
                                @foreach ( $management->subscriptions as $subscription )
                                    <li class="list-group-item" data-subscribe="{{ $subscription->id }}">
                                        {{ $subscription->getName() }}
                                        @if ( $subscription->username )
                                            <strong>&#64;{{ $subscription->username }}</strong>
                                        @endif
                                        <small>[{{ $subscription->telegram_id }}]</small>
                                        <a href="javascript:;" class="badge badge-danger" data-action="unsubscribe" data-id="{{ $subscription->id }}">
                                            <i class="fa fa-remove"></i>
                                            отписать
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                    </div>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            Пользователи
                            <span class="badge">{{ $management->users->count() }}</span>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <ul class="list-group">
                            @foreach ( $management->users as $user )
                                @if ( \Auth::user()->can( 'admin.users.edit' ) )
                                    <a href="{{ route( 'users.edit', $user->id ) }}" class="list-group-item">
                                        {!! $user->getFullName() !!}
                                    </a>
                                @else
                                    <li class="list-group-item">
                                        {!! $user->getFullName() !!}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>

            </div>

        </div>

        <ul class="nav nav-tabs">
            <li class="active">
                <a data-toggle="tab" href="#addresses">
                    Здания
                    <span class="badge" id="addresses-count">{{ $managementAddresses->count() }}</span>
                </a>
            </li>
            <li>
                <a data-toggle="tab" href="#types">
                    Классификатор
                    <span class="badge" id="managements-count">{{ $managementTypes->count() }}</span>
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div id="addresses" class="tab-pane fade in active">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row margin-bottom-20">
                            <div class="col-xs-12">
                                <button id="add-addresses" data-id="{{ $management->id }}" class="btn btn-default">
                                    <i class="glyphicon glyphicon-plus"></i>
                                    Добавить Здания
                                </button>
                            </div>
                        </div>
                        @if ( ! $managementAddresses->count() )
                            @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                        @endif
                        @foreach ( $managementAddresses as $r )
                            <div class="margin-bottom-5">
                                <button type="button" class="btn btn-xs btn-danger" data-delete="management-address" data-management="{{ $management->id }}" data-address="{{ $r->id }}">
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
            <div id="types" class="tab-pane fade">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row margin-bottom-20">
                            <div class="col-xs-12">
                                <button id="add-types" data-id="{{ $management->id }}" class="btn btn-default">
                                    <i class="glyphicon glyphicon-plus"></i>
                                    Добавить Классификатор
                                </button>
                            </div>
                        </div>
                        @if ( ! $managementTypes->count() )
                            @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                        @endif
                        @foreach ( $managementTypes as $r )
                            <div class="margin-bottom-5">
                                <button type="button" class="btn btn-xs btn-danger" data-delete="management-type" data-management="{{ $management->id }}" data-type="{{ $r->id }}">
                                    <i class="fa fa-remove"></i>
                                </button>
                                <a href="{{ route( 'types.edit', $r->id ) }}">
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
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
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

                $( '.datepicker' ).datepicker();

                $( '#address_id' ).select2({
                    minimumInputLength: 3,
                    minimumResultsForSearch: 30,
                    ajax: {
                        data: function ( term, page )
                        {
                            return {
                                q: term.term,
                                region_id: $( '#region_id' ).val()
                            };
                        },
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

            .on( 'click', '#add-types', function ( e )
            {
                e.preventDefault();
                $.get( '{{ route( 'managements.types.add' ) }}', {
                    id: $( this ).attr( 'data-id' )
                }, function ( response )
                {
                    Modal.createSimple( 'Добавить Классификатор', response, 'add-types-modal' );
                });
            })

            .on( 'click', '#add-addresses', function ( e )
            {
                e.preventDefault();
                $.get( '{{ route( 'managements.addresses.add' ) }}', {
                    id: $( this ).attr( 'data-id' )
                }, function ( response )
                {
                    Modal.createSimple( 'Добавить Здания', response, 'add-addresses-modal' );
                });
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

            .on( 'click', '[data-action="unsubscribe"]', function ( e ) {

                e.preventDefault();

                var id = $(this).attr( 'data-id' );

                bootbox.confirm({
                    message: 'Вы уверены, что хотите отменить подписку?',
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

                            $( '[data-subscribe="' + id + '"]' ).remove();

                            $.post( '{{ route( 'managements.unsubscribe' ) }}', {
                                id: id
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