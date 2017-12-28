<tr class="{{ $work->getClass() }}">
    <td>
        #{{ $work->id }}
    </td>
    <td>
        {{ $work->reason }}
    </td>
    <td>
        @foreach ( $work->addresses as $address )
            <div>
                {{ $address->getAddress() }}
            </div>
        @endforeach
    </td>
    <td>
        {{ $work->getCategory() }}
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
    <td>
        @if ( $work->time_end_fact )
            {{ \Carbon\Carbon::parse( $work->time_end_fact )->format( 'd.m.Y H:i' ) }}
        @else
            -
        @endif
    </td>
    <td class="text-right hidden-print" width="30">
        <a href="{{ route( 'works.edit', $work->id ) }}" class="btn btn-lg btn-primary">
            <i class="fa fa-chevron-right"></i>
        </a>
    </td>
</tr>
@if ( $work->comments->count() )
    <tr>
        <td colspan="10">
            <div class="note note-info">
                @include( 'parts.comments', [ 'comments' => $work->comments ] )
            </div>
        </td>
    </tr>
@endif