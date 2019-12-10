<div class="row hidden-print">
    <div class="col-xs-6">
        @can( 'works.export' )
            <a href="{{ route( 'works.export', Request::getQueryString() ) }}" class="btn btn-default btn-lg">
                <i class="fa fa-download"></i>
                Выгрузить в Excel
            </a>
        @endcan
    </div>
    <div class="col-xs-6 text-right">
        @if ( \Input::get( 'show' ) != 'all' && \Auth::user()->can( 'works.report' ) )
            <a href="{{ route( 'works.report', Request::getQueryString() ) }}" class="btn btn-default btn-lg">
                <i class="fa fa-download"></i>
                Выгрузить Отчет
            </a>
        @endcan
    </div>
</div>

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

<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr class="info">
        <th rowspan="2">
            Номер сообщения / Основание / Тип отключения
        </th>
        <th rowspan="2">
            Адрес работ / Исполнитель
        </th>
        <th rowspan="2">
            Категория / Состав работ
        </th>
        <th colspan="3" class="text-center">
            &nbsp;Дата
        </th>
        <th>
            &nbsp;
        </th>
    </tr>
    <tr class="info">
        <th class="text-center">
            ОТ
        </th>
        <th class="text-center">
            ДО
        </th>
        <th class="text-center">
            Факт.
        </th>
        <th>
            &nbsp;
        </th>
    </tr>
    </thead>
    @if ( $works->count() )
        <tbody>
        @foreach ( $works as $work )
            <tr>
                <td>
                    <a href="{{ route( 'works.edit', $work->id ) }}">
                        #{{ $work->id }}
                    </a>
                    <div class="small">
                        {{ $work->reason }}
                    </div>
                    <hr/>
                    {{ \App\Models\Work::$types[ $work->type_id ] ?? '-' }}
                </td>
                <td>
                    @foreach ( $work->getAddressesGroupBySegment( false ) as $segment )
                        @if(count($segment))
                            <div class="margin-top-5">
                            <span class="small">
                                {{ $segment[ 0 ] }}
                            </span>
                                @if ( ! empty( $segment[ 1 ] ) )
                                    <span class="bold">
                                    д. {{ implode( ', ', $segment[ 1 ] ) }}
                                </span>
                                @endif
                            </div>
                        @endif
                    @endforeach
                    <hr/>
                    @foreach ( $work->managements as $management )
                        <div class="small">
                            @if ( $management->parent )
                                <span class="text-muted">
                                {{ $management->parent->name ?? '' }}
                            </span>
                            @endif
                            {{ $management->name }}
                        </div>
                    @endforeach
                </td>
                <td>
                    <div class="bold">
                        {{ $work->category->name ?? '-' }}
                    </div>
                    <hr/>
                    <div class="small">
                        {{ $work->composition }}
                    </div>
                </td>
                <td class="text-center">
                    <div class="small">
                        {{ \Carbon\Carbon::parse( $work->time_begin )->format( 'd.m.Y H:i' ) }}
                    </div>
                </td>
                <td class="text-center">
                    @if ( $work->isExpired() )
                        <div class="bold text-danger">
                            {{ \Carbon\Carbon::parse( $work->time_end )->format( 'd.m.Y H:i' ) }}
                            <p>
                                <i class="fa fa-exclamation"></i>
                                <i class="fa fa-exclamation"></i>
                                <i class="fa fa-exclamation"></i>
                            </p>
                        </div>
                    @else
                        <div class="small">
                            {{ \Carbon\Carbon::parse( $work->time_end )->format( 'd.m.Y H:i' ) }}
                        </div>
                    @endif

                </td>
                <td class="text-center">
                    @if ( $work->time_end_fact )
                        <div class="small">
                            {{ \Carbon\Carbon::parse( $work->time_end_fact )->format( 'd.m.Y H:i' ) }}
                        </div>
                    @else
                        -
                    @endif
                </td>
                <td class="text-right hidden-print text-nowrap">
                    <a class="btn btn-info tooltips" data-action="comment" data-model-name="{{ get_class( $work ) }}"
                       data-model-id="{{ $work->id }}" data-file="1" title="Добавить комментарий">
                        <i class="fa fa-comment"></i>
                    </a>
                    <a href="{{ route( 'works.edit', $work->id ) }}" class="btn btn-primary">
                        <i class="fa fa-chevron-right"></i>
                    </a>
                </td>
            </tr>
            @if ( ! isset( $hideComments ) || ! $hideComments )
                <tr class="comments">
                    <td colspan="7">
                        <div data-work-comments="{{ $work->id }}" class="hidden">
                            <div class="text-center hidden-print">
                                <a class="text-primary small bold" data-toggle="#works-comments-{{ $work->id }}">
                                    Показать \ скрыть комментарии
                                </a>
                            </div>
                            <div class="hidden comments" id="works-comments-{{ $work->id }}"></div>
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
