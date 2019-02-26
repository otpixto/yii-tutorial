<div class="row">
    <div class="col-md-6">
        {{ $tickets->render() }}
    </div>
    <div class="col-md-6 text-right margin-top-10 margin-bottom-10">
        <span class="label label-info">
            Найдено: <b>{{ $tickets->total() }}</b>
        </span>
    </div>
</div>

<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr class="info">
        <th>
            Дата создания
        </th>
        <th>
            Номер заявки
        </th>
        <th>
            Классификатор
            @if ( \Auth::user()->can( 'tickets.field_text' ) )
                \ Текст обращения
            @endif
        </th>
        <th>
            Адрес проблемы \ Заявитель
        </th>
        <th>
            Предпочтительное время
        </th>
        <th>
            &nbsp;
        </th>
    </tr>
    </thead>
    <tbody>
    @if ( $tickets->count() )
        @foreach ( $tickets as $ticket )
            <tr>
                <td>
                    {{ $ticket->created_at->format( 'd.m.Y H:i' ) }}
                </td>
                <td>
                    {{ $ticket->id }}
                </td>
                <td>
                    @if ( $ticket->type )
                        @if ( $ticket->type->parent )
                            <div class="bold">
                                {{ $ticket->type->parent->name }}
                            </div>
                        @endif
                        <div class="small">
                            {{ $ticket->type->name }}
                        </div>
                    @endif
                    @if ( \Auth::user()->can( 'tickets.field_text' ) )
                        <hr />
                        <div class="small">
                            {{ $ticket->text }}
                        </div>
                    @endif
                </td>
                <td>
                    <div>
                        {{ $ticket->getAddress() }}
                        @if ( $ticket->getPlace() )
                            <span class="small text-muted">
                                ({{ $ticket->getPlace() }})
                            </span>
                        @endif
                    </div>
                    <div class="small text-info">
                        {{ $ticket->getName() }}
                    </div>
                    <div class="small">
                        {{ $ticket->getPhones() }}
                    </div>
                </td>
                <td>
                    @if ( $ticket->time_from )
                        с <b>{{ \Carbon\Carbon::parse( $ticket->time_from )->format( 'H:i' ) }}</b>
                    @endif
                    @if ( $ticket->time_to )
                        до <b>{{ \Carbon\Carbon::parse( $ticket->time_to )->format( 'H:i' ) }}</b>
                    @endif
                </td>
                <td class="text-right hidden-print text-nowrap">
                    <a href="{{ route( 'tickets.moderate.reject', $ticket->id ) }}" class="btn btn-danger tooltips" title="Отклонить заявку #{{ $ticket->id }}" data-confirm="Вы уверены, что хотите отклонить заявку?">
                        <i class="fa fa-close"></i>
                    </a>
                    <a href="{{ route( 'tickets.moderate.show', $ticket->id ) }}" class="btn btn-primary tooltips" title="Открыть заявку #{{ $ticket->id }}">
                        <i class="fa fa-chevron-right"></i>
                    </a>
                </td>
            </tr>
        @endforeach
    @endif
    </tbody>
</table>

{{ $tickets->render() }}

@if ( ! $tickets->count() )
    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
@endif