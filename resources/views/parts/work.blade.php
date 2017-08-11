<tr>
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
        {{ $work->datetime_begin }}
    </td>
    <td>
        {{ $work->datetime_end }}
    </td>
    <td>
        {{ $work->text }}
    </td>
    <td class="text-right">
        <a href="{{ route( 'works.show', $work->id ) }}" class="btn btn-lg btn-primary">
            <i class="fa fa-chevron-right"></i>
        </a>
    </td>
</tr>