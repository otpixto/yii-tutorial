@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.subscriptions' ) )

        <div class="row margin-top-15">
            <div class="col-xs-12">

                <div class="row">
                    <div class="col-xs-8">
                        {{ $subscriptions->render() }}
                    </div>
                    <div class="col-xs-4 text-right margin-top-10 margin-bottom-10">
                        <span class="label label-info">
                            Найдено: <b>{{ $subscriptions->total() }}</b>
                        </span>
                    </div>
                </div>

                <table class="table table-hover table-striped">
                    {!! Form::open( [ 'method' => 'get', 'class' => 'submit-loading' ] ) !!}
                    <thead>
                        <tr>
                            <th>
                                УО
                            </th>
                            <th colspan="2">
                                ФИО
                            </th>
                            <th>
                                Telegram ID
                            </th>
                            <th>
                                Username
                            </th>
                            <th colspan="2">
                                Дата подписки
                            </th>
                        </tr>
                        <tr class="hidden-print">
                            <th>
                                {!! Form::text( 'management_name', \Input::old( 'management_name' ), [ 'class' => 'form-control', 'placeholder' => 'УО' ] ) !!}
                            </th>
                            <th>
                                {!! Form::text( 'last_name', \Input::old( 'last_name' ), [ 'class' => 'form-control', 'placeholder' => 'Фамилия' ] ) !!}
                            </th>
                            <th>
                                {!! Form::text( 'first_name', \Input::old( 'first_name' ), [ 'class' => 'form-control', 'placeholder' => 'Имя' ] ) !!}
                            </th>
                            <th>
                                {!! Form::text( 'telegram_id', \Input::old( 'telegram_id' ), [ 'class' => 'form-control', 'placeholder' => 'Telegram ID' ] ) !!}
                            </th>
                            <th>
                                {!! Form::text( 'username', \Input::old( 'username' ), [ 'class' => 'form-control', 'placeholder' => 'Username' ] ) !!}
                            </th>
                            <th colspan="2">
                                {!! Form::text( 'created_at', \Input::old( 'created_at' ), [ 'class' => 'form-control datepicker', 'placeholder' => 'Дата подписки' ] ) !!}
                            </th>
                        </tr>
                        <tr class="hidden-print">
                            <th colspan="7" class="text-right">
                                <span class="text-muted small bold">
                                    Фильтр:
                                </span>
                                <a href="{{ route( 'logs.index' ) }}" class="btn btn-sm btn-default tooltips" title="Очистить фильтр">
                                    <i class="icon-close"></i>
                                    Очистить
                                </a>
                                <button type="submit" class="btn btn-sm btn-primary tooltips bold" title="Применить фильтр">
                                    <i class="icon-check"></i>
                                    Применить
                                </button>
                            </th>
                        </tr>
                    </thead>
                    {!! Form::close() !!}
                    <tbody>
                    @foreach ( $subscriptions as $subscription )
                        <tr>
                            <td>
                                @if ( \Auth::user()->can( 'catalog.managements.edit' ) )
                                    <a href="{{ route( 'managements.edit', $subscription->management_id ) }}">
                                        {{ $subscription->management_name }}
                                    </a>
                                @else
                                    {{ $subscription->management_name }}
                                @endif
                            </td>
                            <td colspan="2">
                                {{ $subscription->getName() }}
                            </td>
                            <td>
                                {{ $subscription->telegram_id }}
                            </td>
                            <td>
                                {{ $subscription->username }}
                            </td>
                            <td>
                                {{ $subscription->created_at->format( 'd.m.Y H:i' ) }}
                            </td>
                            <td class="text-right">
                                {!! Form::model( $subscription, [ 'method' => 'delete', 'route' => [ 'subscriptions.destroy', $subscription->id ], 'data-confirm' => 'Вы уверены, что хотите завершить подписку?', 'class' => 'submit-loading' ] ) !!}
                                <button type="submit" class="btn btn-danger">
                                    <i class="fa fa-close"></i>
                                </button>
                                {!! Form::close() !!}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                @if ( ! $subscriptions->count() )
                    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                @endif

                {{ $subscriptions->render() }}

            </div>
        </div>

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        $( document )
            .ready( function ()
            {

                $( '.datepicker' ).datepicker({
                    format: 'dd.mm.yyyy'
                });

            });
    </script>
@endsection