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
        @if ( $work->buildings->count() > 5 )
            @foreach ( $work->buildings->slice( 0, 5 ) as $building )
                <div class="small">
                    {{ $building->name }}
                </div>
            @endforeach
            <a href="javascript:;" data-toggle="#work-buildings-{{ $work->id }}">
                Показать\ скрыть остальные {{ $work->buildings->count() }}
            </a>
            <div class="display-none" id="work-buildings-{{ $work->id }}">
                @foreach ( $work->buildings->slice( 5 ) as $building )
                    <div class="small">
                        {{ $building->name }}
                    </div>
                @endforeach
            </div>
        @else
            @foreach ( $work->buildings as $building )
                <div class="small">
                    {{ $building->name }}
                </div>
            @endforeach
        @endif
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