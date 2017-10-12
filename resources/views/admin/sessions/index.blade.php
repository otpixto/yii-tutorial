@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row margin-bottom-15">
        <div class="col-xs-12">
            <a href="{{ route( 'sessions.create' ) }}" class="btn btn-success">
                <i class="fa fa-plus"></i>
                Добавить в очередь
            </a>
        </div>
    </div>

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

            {{ $sessions->render() }}

            @if ( $sessions->count() )

                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>
                                Пользователь
                            </th>
                            <th>
                                Номер
                            </th>
                            <th>
                                Начало сессии
                            </th>
                            <th>
                                Окончание сессии
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ( $sessions as $session )
                        <tr>
                            <td>
                                {!! $session->user->getFullName() !!}
                            </td>
                            <td>
                                {{ $session->ext_number }}
                            </td>
                            <td>
                                {{ $session->created_at->format( 'd.m.Y H:i' ) }}
                            </td>
                            <td>
                                {{ $session->deleted_at ? $session->deleted_at->format( 'd.m.Y H:i' ) : '-' }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            @else
                @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
            @endif

            {{ $sessions->render() }}

        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.css" rel="stylesheet" type="text/css" />
@endsection