@if ( $tickets->count() )
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
                Адрес проблемы
            </th>
            <th>
                Классификатор
            </th>
            <th>
                Статус
            </th>
            <th>
                &nbsp;
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach ( $tickets as $ticket )
            <tr>
                <td>
                    <a href="{{ route( 'tickets.index', [ 'ticket_id' => $ticket->id ] ) }}">
                        {{ $ticket->id }}
                    </a>
                </td>
                <td>
                    {{ $ticket->created_at->format( 'd.m.Y H:i' ) }}
                </td>
                <td>
                    {{ $ticket->getAddress() }}
                </td>
                <td>
                    <div>
                        {{ $ticket->type->category->name }}
                    </div>
                    <div>
                        {{ $ticket->type->name }}
                    </div>
                </td>
                <td>
                    {{ $ticket->status_name }}
                </td>
                <td class="text-right">
                    <a href="{{ route( 'tickets.index', [ 'ticket_id' => $ticket->id ] ) }}" class="btn btn-primary btn-xs">
                        <i class="fa fa-chevron-right"></i>
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @if ( $tickets->total() > 15 )
        <a href="{{ $link }}" class="btn btn-info margin-top-15">
            Показать все ({{ $tickets->total() }})
        </a>
    @endif
@else
    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
@endif
