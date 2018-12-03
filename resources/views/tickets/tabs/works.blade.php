@if ( $works->count() )
    <table class="table table-hover table-striped">
        <thead>
        <tr>
            <th>
                №
            </th>
            <th>
                Время отключения
            </th>
            <th>
                Категория
            </th>
            <th>
                Состав работ
            </th>
            <th>
                &nbsp;
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach ( $works as $work )
            <tr>
                <td>
                    <a href="{{ route( 'works.edit', $work->id ) }}">
                        {{ $work->id }}
                    </a>
                </td>
                <td>
                    {{ $work->time_begin->format( 'd.m.Y H:i' ) }}
                    -
                    {{ $work->time_end->format( 'd.m.Y H:i' ) }}
                </td>
                <td>
                    {{ $work->category->name }}
                </td>
                <td>
                    {{ $work->composition }}
                </td>
                <td class="text-right">
                    <a href="{{ route( 'works.edit', $work->id ) }}" class="btn btn-primary btn-xs">
                        <i class="fa fa-chevron-right"></i>
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @if ( $works->total() > 15 )
        <a href="{{ $link }}" class="btn btn-info margin-top-15">
            Показать все ({{ $works->total() }})
        </a>
    @endif
@else
    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
@endif
