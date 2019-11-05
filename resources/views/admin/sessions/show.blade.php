@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Телефонные сессии', route( 'sessions.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.sessions.show' ) )

        <div class="row margin-top-15">
            <div class="col-xs-12">

                @if ( $session->calls()->count() )

                    <table class="table table-hover table-striped">
                        <thead>
                        <tr>
                            <th>
                                Дата звонка
                            </th>
                            <th>
                                Входящий номер
                            </th>
                            <th>
                                Длительность
                            </th>
                            @if ( \Auth::user()->can( 'admin.calls.all' ) )
                                <th>
                                    Запись
                                </th>
                            @endif
                            <th class="text-right">
                                Заявка
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ( $calls as $call )
                            <tr>
                                <td>
                                    {{ \Carbon\Carbon::parse( $call->calldate )->format( 'd.m.Y H:i' ) }}
                                </td>
                                <td>
                                    {{ $call->src }}
                                </td>
                                <td>
                                    {{ date( 'H:i:s', mktime( 0, 0, $call->billsec ) ) }}
                                </td>
                                @if ( \Auth::user()->can( 'admin.calls.all' ) )
                                    <td>
                                        @if ( $call->hasMp3() )
                                            <a href="{{ $call->getMp3() }}" target="_blank">
                                                {{ $call->getMp3() }}
                                            </a>
                                        @else
                                            <span class="text-danger">
                                                Запись не найдена
                                            </span>
                                        @endif
                                    </td>
                                @endif
                                <td class="text-right">
                                    @if ( $call->ticket )
                                        <a href="{{ route( 'tickets.show', $call->ticket->id ) }}">
                                            #{{ $call->ticket->id }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {{ $calls->links() }}

                @else
                    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                @endif

            </div>
        </div>

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection
