<table class="table table-hover table-striped table-condensed">
    <thead>
        <tr>
            <th>
                №
            </th>
            <th>
                Основание
            </th>
            <th>
                Исполнитель работ
            </th>
            <th>
                Состав работ
            </th>
            <th>
                Дата начала
            </th>
            <th>
                Дата окончания
            </th>
        </tr>
    </thead>
    <tbody>
    @foreach ( $works as $work )
        <tr>
            <td>
                <a href="{{ route( 'works.show', $work->id ) }}" target="_blank">
                    {{ $work->id }}
                </a>
            </td>
            <td>
                <span class="small">
                    {{ $work->reason }}
                </span>
            </td>
            <td>
                <span class="small">
                    {{ $work->management->name }}
                </span>
            </td>
            <td>
                <span class="small">
                    {{ $work->composition }}
                </span>
            </td>
            <td>
                <span class="small">
                    {{ $work->time_begin }}
                </span>
            </td>
            <td>
                <span class="small">
                    {{ $work->time_end}}
                </span>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@can ( 'works.show' )
    <div class="margin-top-10">
        <a href="{{ route( 'works.index' ) }}" target="_blank" class="btn btn-primary">
            Показать все отключения за текущий период
        </a>
    </div>
@endcan