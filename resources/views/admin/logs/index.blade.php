@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row hidden-print">
        <div class="col-xs-12">
            {!! Form::open( [ 'method' => 'get' ] ) !!}
            <div class="input-group">
                {!! Form::text( 'search', \Input::get( 'search' ), [ 'class' => 'form-control input-lg', 'placeholder' => 'Быстрый поиск...' ] ) !!}
                <span class="input-group-btn">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-search"></i>
                            Поиск
                        </button>
                    </span>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    <div class="row margin-top-15">
        <div class="col-xs-12">

            {{ $logs->render() }}

            @if ( $logs->count() )

                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>
                                Время
                            </th>
                            <th>
                                Объект
                            </th>
                            <th>
                                ID
                            </th>
                            <th>
                                Автор
                            </th>
                            <th>
                                Текст
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ( $logs as $log )
                        <tr>
                            <td>
                                {{ $log->created_at->format( 'd.m.Y H:i' ) }}
                            </td>
                            <td>
                                {{ $log->model_name }}
                            </td>
                            <td>
                                {{ $log->model_id }}
                            </td>
                            <td>
                                {!! $log->author->getFullName() !!}
                            </td>
                            <td>
                                {{ $log->text }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            @else
                @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
            @endif

            {{ $logs->render() }}

        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.css" rel="stylesheet" type="text/css" />
@endsection