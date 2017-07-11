@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Обращения' ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row margin-bottom-15">
        <div class="col-xs-12">
            <a href="{{ route( 'tickets.create' ) }}" class="btn btn-success">
                <i class="fa fa-plus"></i>
                Добавить обращение
            </a>
        </div>
    </div>

    <div class="search-page search-content-4">
        <div class="search-bar bordered">
            <div class="row">
                <div class="col-lg-8">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Найти...">
                        <span class="input-group-btn">
                            <button class="btn green-soft uppercase bold" type="button">Поиск</button>
                        </span>
                    </div>
                </div>
                <div class="col-lg-4 extra-buttons">
                    <button class="btn grey-steel uppercase bold" type="button">Сбросить</button>
                    <button class="btn grey-cararra font-blue" type="button">Расширенный поиск</button>
                </div>
            </div>
        </div>
        <div class="search-table table-responsive">

            @if ( $tickets->count() )

                {{ $tickets->render() }}

                <table class="table table-bordered table-striped table-condensed">
                    <thead class="bg-blue">
                    <tr>
                        <th width="50">
                            <a href="javascript:;">Статус</a>
                        </th>
                        <th>
                            <a href="javascript:;">Дата</a>
                        </th>
                        <th>
                            <a href="javascript:;">Адрес и заявитель</a>
                        </th>
                        <th>
                            <a href="javascript:;">Описание</a>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ( $tickets as $ticket )
                        <tr>
                            <td class="table-status">
                                <a href="javascript:;">
                                    <i class="icon-arrow-right font-blue"></i>
                                </a>
                            </td>
                            <td class="table-date font-blue">
                                <a href="javascript:;">
                                    {{ $ticket->created_at }}
                                </a>
                            </td>
                            <td class="table-title">
                                <h3>
                                    <a href="javascript:;">
                                        {{ $ticket->address }}
                                    </a>
                                </h3>
                                <p>
                                    <a href="javascript:;">
                                        {{ $ticket->getName() }}
                                    </a> -
                                    <span class="font-grey-cascade">25 mins ago</span>
                                </p>
                            </td>
                            <td class="table-desc">
                                {{ $ticket->text }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                {{ $tickets->render() }}

            @else
                @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
            @endif

        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/pages/css/search.min.css" rel="stylesheet" type="text/css" />
@endsection