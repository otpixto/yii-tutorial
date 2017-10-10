@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Реестр заявок', route( 'tickets.index' ) ],
        [ 'Заявка #' . $ticketManagement->getTicketNumber(), route( 'tickets.open', $ticketManagement->getTicketNumber() ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <h2>История статусов</h2>
    <table class="table table-hover table-striped">
        <thead>
            <tr>
                <th width="150">
                    Дата, время
                </th>
                <th width="30%">
                    Автор
                </th>
                <th>
                    Статус
                </th>
            </tr>
        </thead>
        <tbody>
        @foreach ( $statuses as $status )
            <tr>
                <td>
                    {{ $status->created_at->format( 'd.m.Y H:i' ) }}
                </td>
                <td>
                    {!! $status->author->getFullName() !!}
                </td>
                <td>
                    {{ $status->status_name }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2>История изменений</h2>
    <table class="table table-hover table-striped">
        <thead>
            <tr>
                <th width="150">
                    Дата, время
                </th>
                <th width="30%">
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
                    {!! $log->author->getFullName() !!}
                </td>
                <td>
                    {{ $log->text }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection