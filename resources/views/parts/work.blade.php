<tr class="{{ \Input::get( 'show' ) == 'all' ? $work->getClass() : '' }}">
    <td>
        #{{ $work->id }}
    </td>
    <td>
        {{ $work->reason }}
    </td>
    <td>
        {{ $work->address->name }}
    </td>
    <td>
        {{ $work->type->name }}
    </td>
    <td>
        {{ $work->management->name }}
    </td>
    <td>
        {{ $work->composition }}
    </td>
    <td>
        {{ \Carbon\Carbon::parse( $work->time_begin )->format( 'd.m.Y H:i' ) }}
    </td>
    <td>
        {{ \Carbon\Carbon::parse( $work->time_end )->format( 'd.m.Y H:i' ) }}
    </td>
    <td class="text-right">
        <a href="{{ route( 'works.edit', $work->id ) }}" class="btn btn-lg btn-primary">
            <i class="fa fa-chevron-right"></i>
        </a>
    </td>
</tr>
@if ( $work->comments->count() )
    <tr>
        <td colspan="9">
            <div class="note note-info">
                @include( 'parts.comments', [ 'comments' => $work->comments ] )
            </div>
        </td>
    </tr>
@endif