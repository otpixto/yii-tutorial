<tr>
    @if ( $ticket->group_uuid )
        @if ( ! $ticket->parent_id )
            <td colspan="2" class="border-left">
        @else
            <td width="30" class="border-left text-center">
                &nbsp;
            </td>
            <td>
        @endif
    @else
        <td colspan="2">
    @endif
        <div class="mt-element-ribbon">
            <div class="ribbon ribbon-clip ribbon-shadow ribbon-color-{{ $ticket->getClass() }}">
                <div class="ribbon-sub ribbon-clip ribbon-round"></div>
                <a href="{{ route( 'tickets.show', $ticket->id ) }}" class="color-inherit">
                    {{ $ticket->status_name }}
                </a>
            </div>
        </div>
        <div class="clearfix"></div>
        @if ( $ticket->canGroup() )
            <label class="mt-checkbox">
                <input type="checkbox" name="tickets[]" value="{{ $ticket->id }}" />
                <span></span>
                #{{ $ticket->id }}
            </label>
        @else
            #{{ $ticket->id }}
        @endif
        @if ( $ticket->rate )
            <span class="pull-right">
                @include( 'parts.rate', [ 'ticket' => $ticket ] )
            </span>
        @endif
    </td>
    <td>
        {{ $ticket->created_at->format( 'd.m.Y H:i' ) }}
    </td>
    <td>
        {{ $ticket->author->getShortName() }}
    </td>
    <td>
        @foreach ( $ticket->managements as $ticketManagement )
            <div>
                {{ $ticketManagement->management->name }}
            </div>
        @endforeach
    </td>
    <td>
        <div class="bold">
            {{ $ticket->type->category->name }}
        </div>
        <div class="small">
            {{ $ticket->type->name }}
        </div>
    </td>
    <td>
        {{ $ticket->address->name }}
    </td>
    <td class="text-right">
        <a href="{{ route( 'tickets.show', $ticket->id ) }}" class="btn btn-lg btn-primary tooltips" title="Открыть обращение #{{ $ticket->id }}">
            <i class="fa fa-chevron-right"></i>
        </a>
    </td>
</tr>
@if ( $ticket->comments->count() )
    <tr>
        @if ( $ticket->group_uuid )
            @if ( ! $ticket->parent_id )
                <td colspan="8" class="border-left">
            @else
                <td width="30" class="border-left text-center">
                    &nbsp;
                </td>
                <td colspan="7">
            @endif
        @else
            <td colspan="8">
        @endif
            <div class="note note-info">
                @include( 'parts.comments', [ 'ticket' => $ticket, 'comments' => $ticket->comments ] )
            </div>
        </td>
    </tr>
@endif