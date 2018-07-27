<table class="table table-hover table-striped table-condensed">
    <thead>
        <tr>
            <th class="small">
                № \ Дата
            </th>
            <th class="small">
                Тип заявки
            </th>
            <th class="small">
                Адрес проблемы
            </th>
        </tr>
    </thead>
    <tbody>
    @foreach ( $tickets as $ticket )
        <tr>
            <td>
                <a href="{{ route( 'tickets.show', $ticket->id ) }}" target="_blank" class="small bold">
                    {{ $ticket->id }}
                </a>
                <div class="small text-muted">
                    {{ $ticket->created_at->format( 'd.m.Y H:i' ) }}
                </div>
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