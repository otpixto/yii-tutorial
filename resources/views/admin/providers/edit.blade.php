@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Поставщики', route( 'providers.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin )

        <div class="row">

            <div class="col-lg-6">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Редактрировать</h3>
                    </div>
                    <div class="panel-body">

                        {!! Form::model( $provider, [ 'method' => 'put', 'route' => [ 'providers.update', $provider->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                        <div class="form-group">

                            <div class="col-xs-6">
                                {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                                {!! Form::text( 'name', \Input::old( 'name', $provider->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
                            </div>
                            <div class="col-xs-6">
                                {!! Form::label( 'domain', 'Домен', [ 'class' => 'control-label' ] ) !!}
                                {!! Form::text( 'domain', \Input::old( 'domain', $provider->domain ), [ 'class' => 'form-control', 'placeholder' => 'Домен' ] ) !!}
                            </div>

                        </div>

                        <div class="form-group margin-top-15">

                            <div class="col-xs-12">
                                {!! Form::label( 'need_act', 'Требовать акт выполненных работ', [ 'class' => 'control-label' ] ) !!}
                                {!! Form::checkbox( 'need_act', 1, \Input::old( 'need_act', $provider->need_act ) ) !!}
                            </div>

                        </div>

                        <div class="form-group margin-top-15">

                            <div class="col-xs-12">
                                {!! Form::label( 'sms_auth', 'Двухфакторная авторизация', [ 'class' => 'control-label' ] ) !!}
                                {!! Form::checkbox( 'sms_auth', 1, \Input::old( 'sms_auth', $provider->sms_auth ) ) !!}
                            </div>

                        </div>

                        <div class="form-group hidden-print">
                            <div class="col-xs-6">
                                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                            </div>
                            <div class="col-xs-6 text-right">
                                <a href="{{ route( 'buildings.index', [ 'provider_id' => $provider->id ] ) }}" class="btn btn-default btn-circle">
                                    Здания
                                    <span class="badge">
                                        {{ $provider->buildings()->count() }}
                                    </span>
                                </a>
                                <a href="{{ route( 'managements.index', [ 'provider_id' => $provider->id ] ) }}" class="btn btn-default btn-circle">
                                    УО
                                    <span class="badge">
                                        {{ $provider->managements()->count() }}
                                    </span>
                                </a>
                                <a href="{{ route( 'types.index', [ 'provider_id' => $provider->id ] ) }}" class="btn btn-default btn-circle">
                                    Классификатор
                                    <span class="badge">
                                        {{ $provider->types()->count() }}
                                    </span>
                                </a>
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
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        Номер
                                    </th>
                                    <th>
                                        Наименование
                                    </th>
                                    <th>
                                        Описание
                                    </th>
                                    <th>

                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ( $provider->phones as $phone )
                                <tr>
                                    <td>
                                        {{ $phone->phone }}
                                    </td>
                                    <td>
                                        {{ $phone->name }}
                                    </td>
                                    <td>
                                        {{ $phone->description }}
                                    </td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-xs btn-danger" data-delete="provider-phone" data-phone="{{ $phone->id }}">
                                            <i class="fa fa-remove"></i>
                                        </button>
                                        <a href="{{ route( 'providers.phones.edit', [ $provider->id, $phone->id ] ) }}" class="btn btn-info btn-xs">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="margin-top-15">
                            <a href="{{ route( 'providers.phones.create', $provider->id ) }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i>
                                Добавить
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <div class="row">
            <div class="col-xs-12">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Ключи доступа</h3>
                    </div>
                    <div class="panel-body">

                        {!! Form::model( $provider, [ 'method' => 'put', 'route' => [ 'providers.update', $provider->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        Ключ
                                    </th>
                                    <th>
                                        Описание
                                    </th>
                                    <th>
                                        Разрешенные IP
                                    </th>
									<th>
                                        Разрешенные Referer
                                    </th>
                                    <th class="text-center">
                                        Последний запрос
                                    </th>
                                    <th class="text-center">
                                        Активные токены
                                    </th>
                                    <th>

                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ( $provider->providerKeys as $providerKey )
                                <tr>
                                    <td>
                                        {{ $providerKey->api_key }}
                                    </td>
                                    <td>
                                        {{ $providerKey->description }}
                                    </td>
                                    <td>
                                        {!! $providerKey->ip ? nl2br( $providerKey->ip ) : '-' !!}
                                    </td>
									<td>
                                        {!! $providerKey->referer ? nl2br( $providerKey->referer ) : '-' !!}
                                    </td>
                                    <td class="text-center">
                                        @if ( $providerKey->active_at )
                                            {{ $providerKey->active_at->format( 'd.m.Y H:i' ) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route( 'providers.keys.edit', [ $provider->id, $providerKey->id ] ) }}#tokens" class="badge {{ $providerKey->providerTokens->count() ? 'badge-success' : '' }}">
                                            {{ $providerKey->providerTokens->count() }}
                                        </a>
                                    </td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-xs btn-danger" data-delete="provider-key" data-id="{{ $providerKey->id }}">
                                            <i class="fa fa-remove"></i>
                                        </button>
                                        <a href="{{ route( 'providers.keys.edit', [ $provider->id, $providerKey->id ] ) }}" class="btn btn-info btn-xs">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="margin-top-15">
                            <a href="{{ route( 'providers.keys.create', $provider->id ) }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i>
                                Добавить
                            </a>
                        </div>

                        {!! Form::close() !!}

                    </div>

                </div>

            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">АИС ГЖИ</h3>
                    </div>
                    <div class="panel-body">

                        {!! Form::model( $provider, [ 'method' => 'put', 'route' => [ 'providers.update', $provider->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                        <div class="form-group">

                            <div class="col-xs-4">
                                {!! Form::label( 'guid', 'GUID', [ 'class' => 'control-label' ] ) !!}
                                {!! Form::text( 'guid', \Input::old( 'guid', $provider->guid ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
                            </div>
                            <div class="col-xs-4">
                                {!! Form::label( 'username', 'Логин', [ 'class' => 'control-label' ] ) !!}
                                {!! Form::text( 'username', \Input::old( 'username', $provider->username ), [ 'class' => 'form-control', 'placeholder' => 'Логин' ] ) !!}
                            </div>
                            <div class="col-xs-4">
                                {!! Form::label( 'password', 'Пароль', [ 'class' => 'control-label' ] ) !!}
                                {!! Form::text( 'password', \Input::old( 'password', $provider->password ), [ 'class' => 'form-control', 'placeholder' => 'Пароль' ] ) !!}
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
        </div>

        <div class="row">
            <div class="col-xs-12">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Логотип</h3>
                    </div>
                    <div class="panel-body" style="background-color: #2f373e;">
                        <div class="row">
                            <div class="col-md-3">
                                @if ( $provider->logo )
                                    <img src="/storage/{{ $provider->logo }}" class="img-responsive" />
                                @else
                                    <img src="{{ \App\Models\Provider::getDefaultLogo() }}" class="img-responsive" />
                                @endif
                            </div>
                            {!! Form::model( $provider, [ 'method' => 'put', 'route' => [ 'providers.logo.upload', $provider->id ], 'class' => 'form-horizontal submit-loading', 'files' => true ] ) !!}
                            <div class="col-md-5">
                                {!! Form::file( 'file', [ 'class' => 'form-control' ] ) !!}
                            </div>
                            <div class="col-md-4">
                                {!! Form::submit( 'Загрузить', [ 'class' => 'btn btn-primary' ] ) !!}
                            </div>
                        </div>
                        @if ( $provider->logo )
                            <div class="row margin-top-30">
                                <div class="col-md-3 text-center">
                                    {!! Form::close() !!}
                                    {!! Form::model( $provider, [ 'method' => 'delete', 'route' => [ 'providers.logo.delete', $provider->id ], 'class' => 'form-horizontal submit-loading', 'files' => true ] ) !!}
                                    {!! Form::submit( 'Удалить', [ 'class' => 'btn btn-danger' ] ) !!}
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'js' )
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

            })

            .on( 'click', '[data-delete="provider-phone"]', function ( e )
            {

                e.preventDefault();

                var phone_id = $( this ).attr( 'data-phone' );
                var obj = $( this ).closest( 'tr' );

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

                            obj.hide();

                            $.ajax({
                                url: '{{ route( 'providers.phones.del', $provider->id ) }}',
                                method: 'delete',
                                data: {
                                    phone_id: phone_id
                                },
                                success: function ()
                                {
                                    obj.remove();
                                },
                                error: function ( e )
                                {
                                    obj.show();
                                    alert( e.statusText );
                                }
                            });

                        }
                    }
                });

            })

            .on( 'click', '[data-delete="provider-key"]', function ( e )
            {

                e.preventDefault();

                var key_id = $( this ).attr( 'data-id' );
                var obj = $( this ).closest( 'tr' );

                bootbox.confirm({
                    message: 'Удалить ключ?',
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

                            obj.hide();

                            $.ajax({
                                url: '{{ route( 'providers.keys.del', $provider->id ) }}',
                                method: 'delete',
                                data: {
                                    key_id: key_id
                                },
                                success: function ()
                                {
                                    obj.remove();
                                },
                                error: function ( e )
                                {
                                    obj.show();
                                    alert( e.statusText );
                                }
                            });

                        }
                    }
                });

            });

    </script>
@endsection