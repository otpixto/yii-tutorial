<tr class="{{ $ticket->getColor() }}">
    <td>
        <input type="checkbox" name="tickets[]" value="{{ $ticket->id }}" />
    </td>
    <td class="table-status">
        <a href="{{ route( 'tickets.show', $ticket->id ) }}">
            <i class="icon-arrow-right font-blue hidden"></i>
            {{ $ticket->status_name }}
        </a>
    </td>
    <td class="table-date font-blue">
        <a href="{{ route( 'tickets.show', $ticket->id ) }}">
            {{ $ticket->created_at }}
        </a>
    </td>
    <td class="table-title">
        <h3>
            <a href="{{ route( 'tickets.show', $ticket->id ) }}">
                {{ $ticket->address }}
            </a>
        </h3>
        <p>
            <a href="{{ route( 'tickets.show', $ticket->id ) }}">
                {{ $ticket->getName() }}
            </a>
        </p>
        <p>
            <span class="font-grey-cascade">
                {!! $ticket->getPhones( true ) !!}
            </span>
        </p>
    </td>
    <td class="table-desc">
        <h4>
            <div class="small bold margin-bottom-5">
                {{ $ticket->type->category->name }}
            </div>
            {{ $ticket->type->name }}
        </h4>
        <p>
            {{ $ticket->text }}
        </p>
        @if ( $ticket->group_uuid )
            <a href="?group={{ $ticket->group_uuid }}" class="badge badge-info">
                Группа: {{ $ticket->group_uuid }}
            </a>
        @endif
    </td>
</tr>