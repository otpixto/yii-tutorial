{{--<div class="row">
    <div class="col-md-2">
        <label class="mt-checkbox mt-checkbox-outline">
            {!! Form::checkbox( 'ids[]', $ticketManagement->id, false, [ 'class' => 'ticket-checkbox' ] ) !!}
            <input type="checkbox" >
            <b>#{{ $ticketManagement->ticket->id }}</b><small class="text-muted">/{{ $ticketManagement->id }}</small>
            <span></span>
        </label>
    </div>
</div>--}}
<tr class="tickets @if ( in_array( $ticketManagement->status_code, \App\Models\Ticket::$final_statuses ) ) text-muted opacity @elseif ( $ticketManagement->ticket->emergency ) danger @endif" id="ticket-management-{{ $ticketManagement->id }}" data-ticket-management="{{ $ticketManagement->id }}" data-ticket="{{ $ticketManagement->ticket->id }}">
    <td>
        <div class="mt-element-ribbon">
            <div class="ribbon ribbon-clip ribbon-shadow ribbon-color-{{ $ticketManagement->getClass() }}">
                <div class="ribbon-sub ribbon-clip ribbon-round"></div>
                <a href="{{ route( 'tickets.show', $ticketManagement->getTicketNumber() ) }}" class="color-inherit">
                    {{ $ticketManagement->status_name ?: '-' }}
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
        <div class="small text-muted">
            {{ $ticketManagement->created_at->format( 'd.m.Y H:i' ) }}
        </div>
        @if ( $ticketManagement->rate )
            <span class="pull-right">
                @include( 'tickets.parts.rate', [ 'ticketManagement' => $ticketManagement ] )
            </span>
        @endif
        @if ( $ticketManagement->ticket->owner_id == \Auth::user()->id )
            <div class="margin-top-15">
                <span class="label label-warning bold">
                    Принадлежит мне
                </span>
            </div>
        @endif
        @if ( \Auth::user()->can( 'tickets.field_operator' ) )
            <div class="text-info small bold">
                {{ $ticketManagement->ticket->author->getName() }}
            </div>
        @endif
    </td>
    {{--<td>
        <div class="small">
            <span class="text-muted">
                Создано
            </span>
            <b>{{ $ticketManagement->ticket->created_at->format( 'd.m.Y H:i' ) }}</b>
        </div>
        <div class="small">
            <span class="text-muted">
                Принять до
            </span>
            <span class="bold {{ $ticketManagement->ticket->overdueDeadlineAcceptance() ? 'text-danger' : 'text-success' }}">
                @if ( $ticketManagement->ticket->deadline_acceptance )
                    {{ $ticketManagement->ticket->deadline_acceptance->format( 'd.m.Y H:i' ) }}
                @else
                    -
                @endif
            </span>
        </div>
        <div class="small">
            <span class="text-muted">
                Выполнить до
            </span>
            <span class="bold {{ $ticketManagement->ticket->overdueDeadlineExecution() ? 'text-danger' : 'text-success' }}">
                @if ( $ticketManagement->ticket->deadline_execution )
                    {{ $ticketManagement->ticket->deadline_execution->format( 'd.m.Y H:i' ) }}
                @else
                    -
                @endif
            </span>
        </div>
        @if ( $ticketManagement->ticket->postponed_to )
            <div class="small">
                <span class="text-muted">
                    Отложено до
                </span>
                <span class="bold {{ $ticketManagement->ticket->overdueDeadlinePostponed() ? 'text-danger' : 'text-success' }}">
                {{ $ticketManagement->ticket->postponed_to->format( 'd.m.Y H:i' ) }}
            </span>
            </div>
        @endif
    </td>--}}

    <td>
        @if ( \Auth::user()->can( 'tickets.field_management' ) )
            @if ( $ticketManagement->management->parent )
                <div class="text-muted">
                    {{ $ticketManagement->management->parent->name }}
                </div>
            @endif
            <div class="small">
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
            @if ( $ticketManagement->ticket->type->parent )
                <div class="bold">
                    {{ $ticketManagement->ticket->type->parent->name }}
                </div>
            @endif
            <div class="small">
                {{ $ticketManagement->ticket->type->name }}
            </div>
        @endif
        <div class="margin-top-15">
            @if ( $ticketManagement->ticket->emergency )
                <span class="badge badge-danger bold">
                    <i class="icon-fire"></i>
                    Аварийная
                </span>
            @endif
            @if ( $ticketManagement->ticket->dobrodel )
                <span class="badge badge-danger bold">
                    <i class="icon-heart"></i>
                    Добродел
                </span>
            @endif
            @if ( $ticketManagement->ticket->from_lk )
                <span class="badge badge-warning bold">
                    <i class="icon-user-follow"></i>
                    Из ЛК
                </span>
            @endif
            @if ( $ticketManagement->ticket->from_mosreg )
                <span class="badge badge-danger bold">
                    <i class="icon-support"></i>
                    Из Мосрега
                </span>
            @endif
        </div>
        @if ( \Auth::user()->can( 'tickets.field_text' ) )
            <hr />
            <div class="small">
                {{ $ticketManagement->ticket->text }}
            </div>
        @endif
        {{--@if ( \Auth::user()->can( 'tickets.services.show' ) && $ticketManagement->services->count() )
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
        @endif--}}
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
        <div class="small">
            {{ $ticketManagement->ticket->getPhones() }}
        </div>
    </td>
    <td class="text-right hidden-print text-nowrap">
        <a class="btn btn-info tooltips" data-action="comment" data-model-name="{{ get_class( $ticketManagement->ticket ) }}" data-model-id="{{ $ticketManagement->ticket->id }}" data-file="1" title="Добавить комментарий">
            <i class="fa fa-comment"></i>
        </a>
        <a href="{{ route( 'tickets.show', $ticketManagement->getTicketNumber() ) }}" class="btn btn-{{ in_array( $ticketManagement->status_code, \App\Models\Ticket::$final_statuses ) ? 'info' : 'primary' }} tooltips" title="Открыть заявку #{{ $ticketManagement->getTicketNumber() }}">
            <i class="fa fa-chevron-right"></i>
        </a>
    </td>
</tr>
@if ( ! isset( $hideComments ) || ! $hideComments )
    <tr class="comments @if ( $ticketManagement->ticket->isFinalStatus() ) text-muted opacity @endif">
        <td colspan="5">
            @if ( $ticketManagement->ticket->status_code == 'waiting' && ! empty( $ticketManagement->ticket->postponed_comment ) )
                <div class="note note-warning">
                    <span class="small text-muted">Комментарий к отложенной заявке:</span>
                    {{ $ticketManagement->ticket->postponed_comment }}
                </div>
            @endif
            @if ( isset( $ticketManagement ) && $ticketManagement->rate_comment )
                <div class="note note-danger">
                    <span class="small text-muted">Комментарий к оценке:</span>
                    {{ $ticketManagement->rate_comment }}
                </div>
            @endif
            <div data-ticket-comments="{{ $ticketManagement->ticket->id }}" class="hidden">
                <div class="text-center hidden-print">
                    <a class="text-primary small bold" data-toggle="#tickets-comments-{{ $ticketManagement->id }}">
                        Показать \ скрыть комментарии
                    </a>
                </div>
                <div class="hidden comments" id="tickets-comments-{{ $ticketManagement->id }}"></div>
            </div>
        </td>
    </tr>
@endif
