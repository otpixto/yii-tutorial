<table border="0" cellspacing="0" cellpadding="10" width="100%">
    @php( $count = 5 )
    @foreach ( $services as $service )
        <tr>
            <td  style="border-bottom:1px solid black;padding: 0;">
                {{ $service->name }}
                ({{ $service->quantity . $service->unit }})
            </td>
        </tr>
        @php( $count-- )
    @endforeach
    @for ( $i = $count; $i > 0; $i -- )
        <tr>
            <td  style="border-bottom:1px solid black;padding: 0;">
                &nbsp;
            </td>
        </tr>
    @endfor
</table>