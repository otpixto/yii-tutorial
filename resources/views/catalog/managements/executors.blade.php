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

        <div class="well">
            <a href="{{ route( 'managements.edit', $management->id ) }}">
                @if ( $management->parent )
                    <div class="text-muted">
                        {{ $management->parent->name }}
                    </div>
                @endif
                {{ $management->name }}
            </a>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-plus"></i>
                    Добавить Исполнителя
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::model( $management, [ 'method' => 'put', 'route' => [ 'managements.executors.add', $management->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                <div class="form-group">
                    <div class="col-md-8">
                        {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'name', '', [ 'class' => 'form-control' ] ) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'phone', '', [ 'class' => 'form-control mask_phone' ] ) !!}
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        {!! Form::submit( 'Добавить', [ 'class' => 'btn btn-success' ] ) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-search"></i>
                    Поиск
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::open( [ 'method' => 'get', 'route' => [ 'managements.executors', $management->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                <div class="form-group">
                    <div class="col-md-12">
                        {!! Form::text( 'search', $search, [ 'class' => 'form-control' ] ) !!}
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        {!! Form::submit( 'Найти', [ 'class' => 'btn btn-success' ] ) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-body">

                {{ $managementExecutors->render() }}

                @if ( ! $managementExecutors->count() )
                    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                @else

                    <table class="table table-hover table-striped">
                        <thead>
                        <tr>
                            <th>
                                Наименование
                            </th>
                            <th>
                                Телефон
                            </th>
                            <th>
                                Пользователь
                            </th>
                            <th class="text-center">
                                Заявки
                            </th>
                            <th class="text-center">
                                Отключения
                            </th>
                            <th class="text-right">
                                &nbsp;
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ( $managementExecutors as $executor )
                            <tr>
                                <td>
                                    {{ $executor->name }}
                                </td>
                                <td>
                                    {{ $executor->getPhone() }}
                                </td>
                                <td>
                                    @if ( $executor->user )
                                        <a href="{{ route( 'users.edit', $executor->user_id ) }}">
                                            {{ $executor->user->getShortName() }}
                                        </a>
                                    @else
                                        <span class="badge badge-danger">
                                            нет
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route( 'tickets.index', [ 'executor_id' => $executor->id ] ) }}" class="badge badge-{{ $executor->tickets->count() ? 'info' : 'default' }} bold">
                                        {{ $executor->tickets->count() }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route( 'works.index', [ 'show' => 'all', 'executor_id' => $executor->id ] ) }}" class="badge badge-{{ $executor->works->count() ? 'info' : 'default' }} bold">
                                        {{ $executor->works->count() }}
                                    </a>
                                </td>
                                <td class="text-right">
                                    @if ( \Auth::user()->can( 'catalog.executors.edit' ) )
                                        <button type="button" class="btn btn-danger" data-delete="management-executor" data-executor="{{ $executor->id }}">
                                            <i class="fa fa-remove"></i>
                                        </button>
                                        <a href="{{ route( 'executors.edit', $executor->id ) }}" class="btn btn-info">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif

                {{ $managementExecutors->render() }}

                {!! Form::model( $management, [ 'method' => 'delete', 'route' => [ 'managements.executors.empty', $management->id ], 'class' => 'form-horizontal submit-loading', 'data-confirm' => 'Вы уверены?' ] ) !!}
                <div class="form-group margin-top-15">
                    <div class="col-md-12">
                        {!! Form::submit( 'Удалить все', [ 'class' => 'btn btn-danger' ] ) !!}
                    </div>
                </div>
                {!! Form::close() !!}

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

            .ready(function()
            {

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

            })

            .on( 'click', '[data-delete="management-executor"]', function ( e )
            {

                e.preventDefault();

                var executor_id = $( this ).attr( 'data-executor' );
                var obj = $( this ).closest( 'tr' );

                bootbox.confirm({
                    message: 'Удалить исполнителя?',
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
                                url: '{{ route( 'managements.executors.del', $management->id ) }}',
                                method: 'delete',
                                data: {
                                    executor_id: executor_id
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