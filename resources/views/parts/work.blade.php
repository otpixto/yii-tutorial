<tr class="{{ $work->getClass() }}">
    <td>
        #{{ $work->id }}
    </td>
    <td>
        <div class="small">
            {{ $work->reason }}
        </div>
    </td>
    <td>
        @foreach ( $work->getAddressesGroupBySegment() as $segment )
            <div class="margin-top-5">
                <span class="small">
                    {{ $segment[ 0 ] }}
                </span>
                <span class="bold">
                    д. {{ implode( ', ', $segment[ 1 ] ) }}
                </span>
            </div>
        @endforeach
    </td>
    <td>
        <div class="small">
            {{ $work->getCategory() }}
        </div>
    </td>
    <td>
        <div class="small">
            @if ( $work->management->parent )
                <div class="text-muted">
                    {{ $work->management->parent->name }}
                </div>
            @endif
            {{ $work->management->name }}
        </div>
    </td>
    <td>
        <div class="small">
            {{ $work->composition }}
        </div>
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
                @include( 'parts.comments', [ 'origin' => $work, 'comments' => $work->comments ] )
            </div>
        </td>
    </tr>
@endif