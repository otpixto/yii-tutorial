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

                <table class="table table-bordered table-striped table-condensed">
                    <thead class="bg-blue">
                    <tr>
                        <th width="50">
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
                        <tr>
                            <td class="table-status">
                                <a href="{{ route( 'tickets.show', $ticket->id ) }}">
                                    <i class="icon-arrow-right font-blue"></i>
                                </a>
                            </td>
                            <td class="table-date font-blue">
                                <a href="{{ route( 'tickets.show', $ticket->id ) }}">
                                    {{ $ticket->created_at }}
                                </a>
                            </td>
                            <td class="table-title">
                                <h3>
                                    <a href="{{ route( 'tickets.show', $ticket->id ) }}">
                                        {{ $ticket->address }}
                                    </a>
                                </h3>
                                <p>
                                    <a href="{{ route( 'tickets.show', $ticket->id ) }}">
                                        {{ $ticket->getName() }}
                                    </a>
                                </p>
                                <p>
                                    <span class="font-grey-cascade">
                                        {!! $ticket->getPhones( true ) !!}
                                    </span>
                                </p>
                            </td>
                            <td class="table-desc">
                                <h3>
                                    {{ $ticket->type->name }}
                                </h3>
                                <p>
                                    {{ $ticket->text }}
                                </p>
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