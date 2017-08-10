<tr>
    <td>
        <div class="mt-element-ribbon">
            <div class="ribbon ribbon-clip ribbon-shadow ribbon-color-{{ $ticket->getClass() }}">
                <div class="ribbon-sub ribbon-clip ribbon-round"></div>
                <a href="{{ route( 'tickets.show', $ticket->id ) }}" class="color-inherit">
                    {{ $ticket->status_name }}
                </a>
            </div>
        </div>
        <div class="clearfix"></div>
        <label class="mt-checkbox">
            <input type="checkbox" name="tickets[]" value="{{ $ticket->id }}" />
            <span></span>
            #{{ $ticket->id }}
        </label>
        @include( 'parts.rate' )
    </td>
    <td>
        {{ $ticket->created_at->format( 'd.m.Y H:i' ) }}
    </td>
    <td>
        {{ $ticket->author->getName() }}
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
        {{ $ticket->address }}
        @if ( $ticket->group_uuid )
            <p>
                <a href="?group={{ $ticket->group_uuid }}" class="badge badge-info">
                    Группа: {{ $ticket->group_uuid }}
                </a>
            </p>
        @endif
    </td>
    <td class="text-right">
        <a href="{{ route( 'tickets.show', $ticket->id ) }}" class="btn btn-lg btn-primary">
            <i class="fa fa-chevron-right"></i>
        </a>
    </td>
</tr>