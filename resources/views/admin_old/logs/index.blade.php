@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row margin-top-15">
        <div class="col-xs-12">

            <div class="row">
                <div class="col-xs-8">
                    {{ $logs->render() }}
                </div>
                <div class="col-xs-4 text-right margin-top-10 margin-bottom-10">
                    <span class="label label-info">
                        Найдено: <b>{{ $logs->total() }}</b>
                    </span>
                </div>
            </div>

            <table class="table table-hover table-striped">
                {!! Form::open( [ 'method' => 'get', 'class' => 'submit-loading' ] ) !!}
                <thead>
                    <tr>
                        <th>
                            Дата
                        </th>
                        <th>
                            Объект
                        </th>
                        <th>
                            ID
                        </th>
                        <th>
                            Пользователь
                        </th>
                        <th>
                            Текст
                        </th>
                    </tr>
                    <tr class="hidden-print">
                        <th>
                            {!! Form::text( 'date', \Input::old( 'date' ), [ 'class' => 'form-control datepicker' ] ) !!}
                        </th>
                        <th>
                            {!! Form::select( 'model_name', $models, \Input::old( 'model_name' ), [ 'class' => 'form-control select2' ] ) !!}
                        </th>
                        <th>
                            {!! Form::text( 'model_id', \Input::old( 'model_id' ), [ 'class' => 'form-control' ] ) !!}
                        </th>
                        <th>
                            {!! Form::select( 'author_id', $users, \Input::old( 'author_id' ), [ 'class' => 'form-control select2' ] ) !!}
                        </th>
                        <th>
                            {!! Form::text( 'text', \Input::old( 'text' ), [ 'class' => 'form-control' ] ) !!}
                        </th>
                    </tr>
                    <tr class="hidden-print">
                        <th colspan="5" class="text-right">
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
                @foreach ( $logs as $log )
                    <tr>
                        <td>
                            {{ $log->created_at->format( 'd.m.Y H:i' ) }}
                        </td>
                        <td>
                            @if ( $log->parent && isset( $log->parent::$name ) )
                                {{ $log->parent::$name }}
                            @endif
                            <span class="small text-muted">
                                ({{ $log->model_name }})
                            </span>
                        </td>
                        <td>
                            {{ $log->model_id }}
                        </td>
                        <td>
                            @if ( $log->author )
                                {!! $log->author->getFullName() !!}
                            @endif
                        </td>
                        <td>
                            {{ $log->text }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            @if ( ! $logs->count() )
                @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
            @endif

            {{ $logs->render() }}

        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        $( document )
            .ready( function ()
            {

                $( '.datepicker' ).datepicker({
                    format: 'dd.mm.yyyy'
                });

                $( '.select2' ).select2();

            });
    </script>
@endsection