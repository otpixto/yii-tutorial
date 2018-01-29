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
                Тип заявки
            </th>
            <th>
                Адрес проблемы
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
                <div class="small bold">
                    {{ $ticket->type->category->name }}
                </div>
                <div class="small">
                    {{ $ticket->type->name }}
                </div>
            </td>
            <td>
                <span class="small">
                    {{ $ticket->getAddress( true ) }}
                </span>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>