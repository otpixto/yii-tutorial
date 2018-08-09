<div class="row">
    <div class="col-md-8">
        {{ $works->render() }}
    </div>
    <div class="col-md-4 text-right margin-top-10 margin-bottom-10">
        <span class="label label-info">
            Найдено: <b>{{ $works->total() }}</b>
        </span>
    </div>
</div>

<div class="row margin-top-15 margin-bottom-15">
    <div class="col-xs-6">
        @can( 'works.export' )
            <a href="?export=data&{{ Request::getQueryString() }}" class="btn btn-default btn-lg hidden">
                <i class="fa fa-download"></i>
                Выгрузить в Excel
            </a>
    </div>
    <div class="col-xs-6 text-right">
            <a href="?export=report&{{ Request::getQueryString() }}" class="btn btn-default btn-lg">
                <i class="fa fa-download"></i>
                Выгрузить Отчет
            </a>
        @endcan
    </div>
</div>

<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr class="info">
        <th>
            Номер сообщения
        </th>
        <th>
            Основание
        </th>
        <th>
            Адрес работ
        </th>
        <th>
            Категория
        </th>
        <th>
            Исполнитель работ
        </th>
        <th>
            Состав работ
        </th>
        <th>
            &nbsp;Дата начала
        </th>
        <th colspan="3">
            &nbsp;Дата окончания (План.|Факт.)
        </th>
    </tr>
    </thead>
    @if ( $works->count() )
        <tbody>
        @foreach ( $works as $work )
            <tr class="{{ $work->getClass() }}">
                <td>
                    <a href="{{ route( 'works.edit', $work->id ) }}">
                        #{{ $work->id }}
                    </a>
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
                        {{ $work->category->name }}
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
        @endforeach
        </tbody>
    @endif
</table>

{{ $works->render() }}

@if ( ! $works->count() )
    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
@endif