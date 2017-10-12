@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.sessions.create' ) )
        <div class="row margin-bottom-15">
            <div class="col-xs-12">
                <a href="{{ route( 'sessions.create' ) }}" class="btn btn-success">
                    <i class="fa fa-plus"></i>
                    Добавить в очередь
                </a>
            </div>
        </div>
    @endif

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
                            <th>
                                Длительность
                            </th>
                            <th>
                                &nbsp;
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
                                {{ $session->number }}
                            </td>
                            <td>
                                {{ $session->created_at->format( 'd.m.Y H:i' ) }}
                            </td>
                            @if ( $session->deleted_at )
                                <td>
                                    {{ $session->deleted_at->format( 'd.m.Y H:i' ) }}
                                </td>
                                <td>
                                    @if ( $session->deleted_at->diffInDays( $session->created_at ) >= 1 )
                                        {{ $session->deleted_at->diffInDays( $session->created_at ) }} д.
                                    @endif
                                    {{ date( 'H:i:s', mktime( 0, 0, $session->deleted_at->diffInSeconds( $session->created_at ) ) ) }}
                                </td>
                            @else
                                <td colspan="2">
                                    -
                                </td>
                            @endif
                            <td>
                                <a href="{{ route( 'sessions.show', $session->id ) }}" class="btn btn-lg btn-primary tooltips" title="Просмотр сессии">
                                    <i class="fa fa-chevron-right"></i>
                                </a>
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