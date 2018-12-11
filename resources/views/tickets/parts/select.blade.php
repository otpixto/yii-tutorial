<ul class="nav nav-tabs margin-top-15 margin-bottom-0">
    @if ( $ticket->type_id && $ticket->building_id )
        <li class="active">
            <a href="#managements">
                Выберите УО
            </a>
        </li>
        @if ( $ticket->phone )
            <li>
                <a href="#customer_tickets">
                    Заявки заявителя
                    <span class="badge {{ $customerTicketsCount ? 'bg-green-jungle bold' : 'bg-grey-salt' }}">
                        {{ $customerTicketsCount }}
                    </span>
                </a>
            </li>
        @endif
        <li>
            <a href="#neighbors_tickets">
                Заявки соседей
                <span class="badge {{ $neighborsTicketsCount ? 'bg-green-jungle bold' : 'bg-grey-salt' }}">
                    {{ $neighborsTicketsCount }}
                </span>
            </a>
        </li>
        <li>
            <a href="#works">
                Отключения
                <span class="badge {{ $worksCount ? 'bg-green-jungle bold' : 'bg-grey-salt' }}">
                    {{ $worksCount }}
                </span>
            </a>
        </li>
    @elseif ( $ticket->phone )
        <li class="active">
            <a href="#customer_tickets">
                Заявки заявителя
                <span class="badge {{ $customerTicketsCount ? 'bg-green-jungle bold' : 'bg-grey-salt' }}">
                    {{ $customerTicketsCount }}
                </span>
            </a>
        </li>
    @endif
</ul>

<div class="tab-content">

    @if ( $ticket->type_id && $ticket->building_id )

        <div id="managements" class="tab-pane fade in active margin-top-15">

            <table class="table table-hover table-striped table-condensed">
                <thead>
                    <tr>
                        <th>

                        </th>
                        <th>
                            УО
                        </th>
                        <th>
                            Контакты
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach ( $managements as $management )
                    <tr>
                        <td class="text-right md-checkbox-inline">
                            <div class="md-checkbox">
                                {!! Form::checkbox( 'managements[]', $management->id, false, [ 'class' => 'md-check', 'id' => 'management-' . $management->id ] ) !!}
                                <label for="management-{{ $management->id }}">
                                    <span class="inc"></span>
                                    <span class="check"></span>
                                    <span class="box"></span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <label for="management-{{ $management->id }}">
                                @if ( $management->parent )
                                    <div class="text-muted">
                                        {{ $management->parent->name }}
                                    </div>
                                @endif
                                <div>
                                    {{ $management->name }}
                                </div>
                            </label>
                            @if ( ! $management->has_contract )
                                <div class="label label-danger bold">
                                    Отсутствует договор
                                </div>
                            @endif
                        </td>
                        <td>
                            {!! $management->getPhones() !!}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            @if ( ! $managements->count() )
                @include( 'parts.error', [ 'error' => 'УО по заданным критериям не найдены' ] )
            @endif

        </div>

        <div id="neighbors_tickets" class="tab-pane fade margin-top-15"></div>
        <div id="customer_tickets" class="tab-pane fade margin-top-15"></div>
        <div id="works" class="tab-pane fade margin-top-15"></div>

    @elseif ( $ticket->phone )
        <div id="customer_tickets" class="tab-pane fade in active margin-top-15">
            @include( 'tickets.tabs.mini_table', [ 'tickets' => $customerTickets ] )
        </div>
    @endif

</div>