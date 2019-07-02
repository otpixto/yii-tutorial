@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Управляющие организации', route( 'managements.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.managements.edit' ) )

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Редактировать</h3>
            </div>
            <div class="panel-body">

                {!! Form::model( $management, [ 'method' => 'put', 'route' => [ 'managements.update', $management->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                <div class="form-group">

                    <div class="col-md-4">
                        {!! Form::label( 'category_id', 'Категория', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'category_id', \App\Models\Management::$categories, \Input::old( 'category_id', $management->category_id ), [ 'class' => 'form-control select2', 'placeholder' => 'Категория' ] ) !!}
                    </div>

                    <div class="col-md-8">
                        {!! Form::label( 'building_id', 'Адрес', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'building_id', $management->building ? $management->building()->pluck( \App\Models\Building::$_table . '.name', \App\Models\Building::$_table . '.id' ) : [], \Input::old( 'building_id', $management->building_id ), [ 'class' => 'form-control select2-ajax', 'placeholder' => 'Адрес офиса', 'data-ajax--url' => route( 'buildings.search' ), 'data-placeholder' => 'Адрес офиса' ] ) !!}
                    </div>

                </div>

                <div class="form-group">

                    <div class="col-md-4">
                        {!! Form::label( 'parent_id', 'Родитель', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'parent_id', $management->parent ? $management->parent()->pluck( \App\Models\Management::$_table . '.name', \App\Models\Management::$_table . '.id' ) : [], \Input::old( 'parent_id', $management->parent_id ), [ 'class' => 'form-control select2-ajax', 'placeholder' => 'Родитель', 'data-ajax--url' => route( 'managements.parents.search', $management->id ), 'data-placeholder' => 'Родитель' ] ) !!}
                    </div>

                    <div class="col-md-8">
                        {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'name', \Input::old( 'name', $management->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
                    </div>

                </div>

                <div class="form-group">

                    <div class="col-md-4">
                        {!! Form::label( 'director', 'ФИО руководителя', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'director', \Input::old( 'director', $management->director ), [ 'class' => 'form-control', 'placeholder' => 'ФИО руководителя' ] ) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'phone', \Input::old( 'phone', $management->phone ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::label( 'phone2', 'Доп. телефон', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'phone2', \Input::old( 'phone2', $management->phone2 ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Доп. телефон' ] ) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::label( 'email', 'E-mail', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::email( 'email', \Input::old( 'email', $management->email ), [ 'class' => 'form-control', 'placeholder' => 'E-mail' ] ) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::label( 'site', 'Сайт', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'site', \Input::old( 'site', $management->site ), [ 'class' => 'form-control', 'placeholder' => 'Сайт' ] ) !!}
                    </div>

                </div>

                <div class="form-group">

                    <div class="col-md-6">
                        {!! Form::label( 'services', 'Услуги', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'services', \Input::old( 'services', $management->services ), [ 'class' => 'form-control', 'placeholder' => 'Услуги' ] ) !!}
                    </div>

                    <div class="col-md-6">
                        {!! Form::label( 'schedule', 'График работы', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'schedule', \Input::old( 'schedule', $management->schedule ), [ 'class' => 'form-control', 'placeholder' => 'График работы' ] ) !!}
                    </div>

                </div>

                <div class="form-group">
                    <div class="col-md-6">
                        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route( 'managements.buildings', $management->id ) }}"
                           class="btn btn-default btn-circle">
                            Адреса
                            <span class="badge">
                                {{ $management->buildings()->count() }}
                            </span>
                        </a>
                        <a href="{{ route( 'managements.types', $management->id ) }}"
                           class="btn btn-default btn-circle">
                            Классификатор
                            <span class="badge">
                                {{ $management->types()->count() }}
                            </span>
                        </a>
                        <a href="{{ route( 'managements.executors', $management->id ) }}"
                           class="btn btn-default btn-circle">
                            Исполнители
                            <span class="badge">
                                {{ $management->executors()->count() }}
                            </span>
                        </a>
                    </div>
                </div>

                {!! Form::close() !!}

            </div>

        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">АИС ГЖИ</h3>
            </div>
            <div class="panel-body">

                {!! Form::model( $management, [ 'method' => 'put', 'route' => [ 'managements.update', $management->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}


                <div class="form-group">

                    <div class="col-md-12 margin-bottom-15">
                        <div class="input-group">
                            <span class="input-group-addon">
                                GUID
                            </span>
                            {!! Form::text( 'guid', \Input::old( 'guid', $management->guid ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
                        </div>
                    </div>

                </div>


                <div class="form-group">

                    <div class="col-md-4 margin-bottom-15">
                        <div class="input-group">
                            <span class="input-group-addon">
                                Mosreg ID
                            </span>
                            {!! Form::text( 'mosreg_id', \Input::old( 'mosreg_id', $management->mosreg_id ), [ 'class' => 'form-control', 'placeholder' => 'Mosreg ID' ] ) !!}
                        </div>
                    </div>

                    <div class="col-md-4 margin-bottom-15">
                        <div class="input-group">
                            <span class="input-group-addon">
                                Логин
                            </span>
                            {!! Form::text( 'mosreg_username', \Input::old( 'mosreg_username', $management->mosreg_username ), [ 'class' => 'form-control', 'placeholder' => 'Логин' ] ) !!}
                        </div>
                    </div>

                    <div class="col-md-4 margin-bottom-15">
                        <div class="input-group">
                            <span class="input-group-addon">
                                Пароль
                            </span>
                            {!! Form::text( 'mosreg_password', \Input::old( 'mosreg_password', $management->mosreg_password ), [ 'class' => 'form-control', 'placeholder' => 'Пароль' ] ) !!}
                        </div>
                    </div>

                </div>

                @if(!empty($management->mosreg_id) && !empty($management->mosreg_username) && !empty($management->mosreg_password))
                    <div class="form-group">

                        @if(!$management->webhook_active)
                            <div class="col-md-4 margin-bottom-15">
                                <a href="{{ route( 'managements.webhook_token.generate', $management->id ) }}"
                                   class="btn btn-default btn-circle btn-warning">Подключить
                                    WEBHOOK</a>
                            </div>
                        @else
                            <div class="col-md-4 margin-bottom-15">
                                <div class="input-group">
                                            <span class="input-group-addon">
                                                Подключенный TOKEN
                                            </span>
                                    {!! Form::text( 'webhook_token', $management->webhook_token , [ 'class' => 'form-control', 'placeholder' => 'Webhook token', 'disabled' => 'disabled' ] ) !!}
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <a href="{{ route( 'managements.webhook_token.generate', $management->id ) }}"
                                   class="btn btn-default btn-circle">Перегенерировать token</a>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route( 'managements.webhook_token.reset', $management->id ) }}"
                                   class="btn btn-default btn-circle btn-danger">Отключить WEBHOOK</a>
                            </div>
                        @endif

                    </div>
                @endif


                <div class="form-group hidden-print">
                    <div class="col-xs-12">
                        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                </div>

                {!! Form::close() !!}

            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    Настройки
                </h3>
            </div>
            <div class="panel-body">

                {!! Form::model( $management, [ 'method' => 'put', 'route' => [ 'managements.contract', $management->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                <div class="form-group">
                    <div class="col-md-2">
                        {!! Form::label( 'has_contract', 'Заключен договор', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'has_contract', [ 0 => 'Нет', 1 => 'Да' ], \Input::old( 'has_contract', $management->has_contract ), [ 'class' => 'form-control' ] ) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label( 'contract_number', 'Номер договора', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'contract_number', $management->contract_number, [ 'class' => 'form-control', 'placeholder' => 'Номер договора' ] ) !!}
                    </div>
                    <div class="col-md-6">
                        {!! Form::label( 'contract_begin', 'Действие договора', [ 'class' => 'control-label' ] ) !!}
                        <div class="input-group">
                            {!! Form::text( 'contract_begin', $management->contract_begin ? $management->contract_begin->format( 'd.m.Y' ) : '', [ 'class' => 'form-control datepicker', 'placeholder' => 'ОТ', 'data-date-format' => 'dd.mm.yyyy' ] ) !!}
                            <span class="input-group-addon">-</span>
                            {!! Form::text( 'contract_end', $management->contract_end ? $management->contract_end->format( 'd.m.Y' ) : '', [ 'class' => 'form-control datepicker', 'placeholder' => 'ДО', 'data-date-format' => 'dd.mm.yyyy' ] ) !!}
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-2">
                        {!! Form::label( 'need_act', 'Требуется акт', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'need_act', [ 0 => 'Нет', 1 => 'Да' ], \Input::old( 'need_act', $management->need_act ), [ 'class' => 'form-control' ] ) !!}
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                </div>

                {!! Form::close() !!}

            </div>

        </div>

        @if ( \Auth::user()->can( 'catalog.managements.acts' ) )

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        Акты
                    </h3>
                </div>
                <div class="panel-body">

                    <div class="list-group">
                        @if ( $management->parent )
                            @foreach ( $management->parent->acts as $act )
                                <a href="{{ route( 'managements.act', [ $management->parent_id, $act->id ] ) }}"
                                   class="list-group-item">
                                    {{ $act->name }}
                                </a>
                            @endforeach
                        @endif
                        @foreach ( $management->acts as $act )
                            <a href="{{ route( 'managements.act', [ $management->id, $act->id ] ) }}"
                               class="list-group-item">
                                {{ $act->name }}
                            </a>
                        @endforeach
                    </div>

                </div>

            </div>

        @endif

        @if ( \Auth::user()->can( 'catalog.managements.telegram' ) )

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        Оповещения в Telegram
                    </h3>
                </div>
                <div class="panel-body">

                    <div class="form-group">
                        @if ( ! $management->telegram_code )
                            <div class="col-md-12">
                                <button type="button" class="btn btn-success" data-action="telegram-on">Подключить
                                </button>
                            </div>
                        @else
                            <div class="col-md-6">
                                <button type="button" class="btn btn-danger" data-action="telegram-off">Отключить
                                </button>
                            </div>
                            <div class="input-group">
                                        <span class="input-group-addon">
                                            Пин-код
                                        </span>
                                {!! Form::text( null, $management->telegram_code, [ 'class' => 'form-control', 'readonly' ] ) !!}
                            </div>
                        @endif
                    </div>

                    @if ( $management->telegram_code )
                        <div class="form-group">
                            <div class="col-md-12">
                                <h3>
                                    Подписки
                                    ({{ $management->subscriptions->count() }})
                                </h3>
                                @if ( $management->subscriptions->count() )
                                    <ul class="list-group">
                                        @foreach ( $management->subscriptions as $subscription )
                                            <li class="list-group-item" data-subscribe="{{ $subscription->id }}">
                                                {{ $subscription->getName() }}
                                                @if ( $subscription->username )
                                                    <strong>&#64;{{ $subscription->username }}</strong>
                                                @endif
                                                <small>[{{ $subscription->telegram_id }}]</small>
                                                <a href="javascript:;" class="badge badge-danger"
                                                   data-action="telegram-unsubscribe" data-id="{{ $subscription->id }}">
                                                    <i class="fa fa-remove"></i>
                                                    отписать
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    @include( 'parts.error', [ 'error' => 'Активных подписок нет' ] )
                                @endif
                            </div>
                        </div>
                    @endif

                </div>

            </div>

        @endif

        @if ( \Auth::user()->can( 'catalog.managements.users' ) )

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        Пользователи
                        <span class="badge">{{ $management->users->count() }}</span>
                    </h3>
                </div>
                <div class="panel-body">
                    @if ( $management->users->count() )
                        <table class="table table-hover table-striped">
                            <thead>
                            <tr>
                                <th>
                                    ФИО
                                </th>
                                <th>
                                    E-mail
                                </th>
                                <th>
                                    Роль
                                </th>
                                @if ( \Auth::user()->can( 'admin.users.edit' ) )
                                    <th>
                                        &nbsp;
                                    </th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ( $management->users as $user )
                                <tr>
                                    <td>
                                        {!! $user->getName( true ) !!}
                                    </td>
                                    <td>
                                        {{ $user->email }}
                                    </td>
                                    <td>
                                        {{ $user->roles->implode( 'name', ', ' ) }}
                                    </td>
                                    @if ( \Auth::user()->can( 'admin.users.edit' ) )
                                        <td class="text-right">
                                            <a href="{{ route( 'users.edit', $user->id ) }}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                    @endif

                </div>
            </div>

        @endif

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet"
          type="text/css"/>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"
            type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js"
            type="text/javascript"></script>
    <script type="text/javascript">

        $(document)

            .ready(function () {

                $('.mask_phone').inputmask('mask', {
                    'mask': '+7 (999) 999-99-99'
                });

                $('.datepicker').datepicker();

            })

            .on('click', '[data-action="telegram-on"]', function (e) {

                e.preventDefault();

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
                    callback: function (result) {
                        if (result) {
                            $.post('{{ route( 'managements.telegram.on', $management->id ) }}', function (response) {
                                window.location.reload();
                            });
                        }
                    }
                });

            })

            .on('click', '[data-action="telegram-off"]', function (e) {

                e.preventDefault();

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

                            $.post('{{ route( 'managements.telegram.off', $management->id ) }}', function (response) {
                                window.location.reload();
                            });

                        }
                    }
                });

            })

            .on('click', '[data-action="telegram-unsubscribe"]', function (e) {

                e.preventDefault();

                var id = $(this).attr('data-id');

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
                    callback: function (result) {
                        if (result) {

                            $('[data-subscribe="' + id + '"]').remove();

                            $.post('{{ route( 'managements.telegram.unsubscribe', $management->id ) }}', {
                                id: id
                            });

                        }
                    }
                });

            });

    </script>
@endsection