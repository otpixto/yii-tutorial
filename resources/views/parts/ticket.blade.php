<tr class="tickets @if ( in_array( $ticketManagement->status_code, \App\Models\Ticket::$final_statuses ) ) text-muted opacity @elseif ( $ticketManagement->ticket->emergency ) danger @endif @if ( isset( $hide ) && $hide ) hidden @endif" id="ticket-management-{{ $ticketManagement->id }}" data-ticket-management="{{ $ticketManagement->id }}" data-ticket="{{ $ticketManagement->ticket->id }}">
    <td>
        <div class="mt-element-ribbon">
            <div class="ribbon ribbon-clip ribbon-shadow ribbon-color-{{ $ticketManagement->getClass() }}">
                <div class="ribbon-sub ribbon-clip ribbon-round"></div>
                <a href="{{ route( 'tickets.show', $ticketManagement->getTicketNumber() ) }}" class="color-inherit">
                    {{ $ticketManagement->status_name }}
                </a>
            </div>
        </div>
        <div class="clearfix"></div>
        @if ( \Auth::user()->can( 'tickets.waybill' ) )
            <label class="mt-checkbox mt-checkbox-outline">
                {!! Form::checkbox( 'ids[]', $ticketManagement->id, false, [ 'class' => 'ticket-checkbox' ] ) !!}
                <input type="checkbox" >
                <b>#{{ $ticketManagement->ticket->id }}</b><small class="text-muted">/{{ $ticketManagement->id }}</small>
                <span></span>
            </label>
        @else
            <b>#{{ $ticketManagement->ticket->id }}</b><small class="text-muted">/{{ $ticketManagement->id }}</small>
        @endif
        @if ( $ticketManagement->rate )
            <span class="pull-right">
                @include( 'parts.rate', [ 'ticketManagement' => $ticketManagement ] )
            </span>
        @endif
    </td>
    <td>
        <span class="small">
            {{ $ticketManagement->ticket->created_at->format( 'd.m.Y H:i' ) }}
        </span>
    </td>
    @if ( $field_operator )
        <td>
            <span class="{{ $ticketManagement->ticket->author->id == \Auth::user()->id ? 'mark' : '' }} small">
                {{ $ticketManagement->ticket->author->getShortName() }}
            </span>
        </td>
    @endif
    <td>
        @if ( $field_management )
            <div>
                {{ $ticketManagement->management->name }}
            </div>
        @endif
        @if ( $ticketManagement->executor )
            <div class="small text-info">
                {{ $ticketManagement->executor->name }}
            </div>
        @endif
    </td>
    <td>
        @if ( $ticketManagement->ticket->type )
            <div class="bold">
                {{ $ticketManagement->ticket->type->category->name }}
            </div>
            <div class="small">
                {{ $ticketManagement->ticket->type->name }}
            </div>
        @endif
        <div class="margin-top-15">
            @if ( $ticketManagement->ticket->emergency )
                <span class="badge badge-danger bold">
                    <i class="icon-fire"></i>
                    Авария
                </span>
            @endif
            @if ( $ticketManagement->ticket->dobrodel )
                <span class="badge badge-danger bold">
                    <i class="icon-heart"></i>
                    Добродел
                </span>
            @endif
            @if ( $ticketManagement->ticket->from_lk )
                <span class="badge badge-danger bold">
                    <i class="icon-user-follow"></i>
                    Из ЛК
                </span>
            @endif
        </div>
        @if ( \Auth::user()->can( 'tickets.services.show' ) && $ticketManagement->services->count() )
            <hr />
            <div class="bold">
                Выполненные работы:
            </div>
            <ol style="margin: 0; padding: 0 15px;">
                @foreach ( $ticketManagement->services as $service )
                    <li class="small">
                        {{ $service->name }}
                    </li>
                @endforeach
            </ol>
        @endif
    </td>
    <td>
        <div>
            {{ $ticketManagement->ticket->getAddress() }}
            @if ( $ticketManagement->ticket->getPlace() )
                <span class="small text-muted">
                ({{ $ticketManagement->ticket->getPlace() }})
            </span>
            @endif
        </div>
        <div class="small text-info">
            {{ $ticketManagement->ticket->getName() }}
        </div>
    </td>
    <td class="text-right hidden-print">
        <a href="{{ route( 'tickets.show', $ticketManagement->getTicketNumber() ) }}" class="btn btn-lg btn-{{ in_array( $ticketManagement->status_code, \App\Models\Ticket::$final_statuses ) ? 'info' : 'primary' }} tooltips" title="Открыть заявку #{{ $ticketManagement->getTicketNumber() }}">
            <i class="fa fa-chevron-right"></i>
        </a>
    </td>
</tr>
@if ( ! isset( $hideComments ) || ! $hideComments )
    @include( 'parts.ticket_comments', [ 'ticket' => $ticketManagement->ticket, 'ticketManagement' => $ticketManagement, 'comments' => $ticketManagement->ticket->getComments() ] )
@endif