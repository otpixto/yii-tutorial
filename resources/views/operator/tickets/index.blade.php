@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Обращения' ]
    ]) !!}
@endsection

@section( 'content' )

    @can( 'tickets.create' )
        <div class="row margin-bottom-15">
            <div class="col-xs-12">
                <a href="{{ route( 'tickets.create' ) }}" class="btn btn-success">
                    <i class="fa fa-plus"></i>
                    Добавить обращение
                </a>
            </div>
        </div>
    @endcan

    <div class="search-page search-content-4">
        {!! Form::open( [ 'method' => 'get' ] ) !!}
        <div class="search-bar bordered">
            <div class="row">
                <div class="col-md-12">
                    <div class="input-group">
                        {!! Form::text( 'search', \Input::get( 'search' ), [ 'class' => 'form-control', 'placeholder' => 'Найти...' ] ) !!}
                        <span class="input-group-btn">
                            {!! Form::submit( 'Поиск', [ 'class' => 'btn green-soft uppercase bold' ] ) !!}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}

        <div class="search-table table-responsive">

            @if ( $tickets->count() )

                {{ $tickets->render() }}

                {!! Form::open( [ 'url' => route( 'tickets.action' ) ] ) !!}

                <table class="table table-bordered table-striped table-condensed">
                    <thead class="bg-blue">
                    <tr>
                        <th>
                            &nbsp;
                        </th>
                        <th>
                            <a href="">Статус</a>
                        </th>
                        <th>
                            <a href="">Дата</a>
                        </th>
                        <th>
                            <a href="">Адрес и заявитель</a>
                        </th>
                        <th>
                            <a href="">Описание</a>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ( $tickets as $ticket )
                        @include( 'parts.ticket', [ 'ticket' => $ticket ] )
                        @if ( $ticket->childs->count() )
                            @foreach ( $ticket->childs as $child )
                                @include( 'parts.ticket', [ 'ticket' => $child ] )
                            @endforeach
                        @endif
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">
                                Действия с выделенными
                            </th>
                            <td colspan="2">
                                @can ( 'tickets.group' )
                                    <button type="submit" name="action" value="group" class="btn btn-default">
                                        Группировать
                                    </button>
                                    <button type="submit" name="action" value="ungroup" class="btn btn-default">
                                        Разгруппировать
                                    </button>
                                @endcan
                                @can ( 'tickets.delete' )
                                    <button type="submit" name="action" value="delete" class="btn btn-danger">
                                        Удалить
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    </tfoot>
                </table>

                {!! Form::close() !!}

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
    <link href="/assets/global/css/colors.css" rel="stylesheet" type="text/css" />
@endsection