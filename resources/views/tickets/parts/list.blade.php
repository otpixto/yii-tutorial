<div class="row">
    <div class="col-md-8">
        {{ $ticketManagements->render() }}
    </div>
    <div class="col-md-4 text-right margin-top-10 margin-bottom-10">
        <span class="label label-info">
            Найдено: <b>{{ $ticketManagements->total() }}</b>
        </span>
        @if ( \Auth::user()->can( 'tickets.export' ) )
            <span class="hidden-print">
                |
                <a href="{{ route( 'tickets.export', Request::getQueryString() ) }}">
                    Выгрузить
                </a>
            </span>
        @endif
    </div>
</div>

<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr class="info">
        <th width="250">
            Статус \ Номер заявки \ Оценка
            @if ( \Auth::user()->can( 'tickets.field_operator' ) )
                \ Автор
            @endif
        </th>
        {{--<th width="220">
            Даты \ Сроки
        </th>--}}
        <th width="200">
            @if ( \Auth::user()->can( 'tickets.field_management' ) )
                УО \
            @endif
            Исполнитель
        </th>
        <th width="300">
            Классификатор
            @if ( \Auth::user()->can( 'tickets.field_text' ) )
                \ Текст обращения
            @endif
            {{--@if ( \Auth::user()->can( 'tickets.services.show' ) )
                \ Выполненные работы
            @endif--}}
        </th>
        <th colspan="2">
            Адрес проблемы \ Заявитель
        </th>
    </tr>
    </thead>
    <tbody id="tickets">
    <tr id="tickets-new-message" class="hidden">
        <td colspan="7">
            <button type="button" class="btn btn-warning btn-block btn-lg" id="tickets-new-show">
                Добавлены новые заявки <span class="badge bold" id="tickets-new-count">2</span>
            </button>
        </td>
    </tr>
    @if ( $ticketManagements->count() )
        @foreach ( $ticketManagements as $ticketManagement )
            @include( 'tickets.parts.line', [ 'ticketManagement' => $ticketManagement ] )
        @endforeach
    @endif
    </tbody>
</table>

{{--@if ( $ticketManagements->count() )
    @foreach ( $ticketManagements as $ticketManagement )
        @include( 'tickets.parts.line', [ 'ticketManagement' => $ticketManagement ] )
    @endforeach
@endif--}}

{{ $ticketManagements->render() }}

@if ( ! $ticketManagements->count() )
    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
@endif