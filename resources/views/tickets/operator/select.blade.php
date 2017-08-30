<div class="alert alert-info">
    Заявитель:
    <b>{{ $customer->getName() }}</b>
</div>
<table class="table table-hover table-striped table-condensed">
    <thead>
        <tr>
            <th>
                №
            </th>
            <th>
                Дата
            </th>
            <th>
                Тип обращения
            </th>
            <th>
                Адрес проблемы
            </th>
            <th>
                Текст обращения
            </th>
        </tr>
    </thead>
    <tbody>
    @foreach ( $tickets as $ticket )
        <tr>
            <td>
                <a href="{{ route( 'tickets.show', $ticket->id ) }}" target="_blank">
                    {{ $ticket->id }}
                </a>
            </td>
            <td>
                <span class="small">
                    {{ $ticket->created_at->format( 'd.m.Y H:i' ) }}
                </span>
            </td>
            <td>
                <span class="small">
                    {{ $ticket->type->name }}
                </span>
            </td>
            <td>
                <span class="small">
                    {{ $ticket->getAddress() }}
                </span>
            </td>
            <td>
                <span class="small">
                    {{ $ticket->text }}
                </span>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@can ( 'tickets.customer_tickets' )
    <div class="margin-top-10">
        <a href="{{ route( 'tickets.customer_tickets', $customer->id ) }}" target="_blank" class="btn btn-primary">
            Показать все обращения заявителя
        </a>
    </div>
@endcan