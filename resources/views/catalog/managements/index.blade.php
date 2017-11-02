@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @can( 'works.create' )
        <div class="row margin-bottom-15">
            <div class="col-xs-6">
                <a href="{{ route( 'managements.create' ) }}" class="btn btn-success btn-lg">
                    <i class="fa fa-plus"></i>
                    Добавить УО
                </a>
            </div>
            <div class="col-xs-6 text-right">
                <a href="?export=1&{{ Request::getQueryString() }}" class="btn btn-default btn-lg">
                    <i class="fa fa-download"></i>
                    Выгрузить в Excel
                </a>
            </div>
        </div>
    @endcan

    <div class="row">
        <div class="col-xs-12">
            {!! Form::open( [ 'method' => 'get' ] ) !!}
            <div class="input-group">
                {!! Form::text( 'search', \Input::get( 'search' ), [ 'class' => 'form-control input-lg', 'placeholder' => 'Быстрый поиск...' ] ) !!}
                <span class="input-group-btn">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-search"></i>
                            Поиск
                        </button>
                    </span>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    <div class="row margin-top-15">
        <div class="col-xs-12">

            @if ( $managements->count() )

                {{ $managements->render() }}

                <table class="table table-hover table-striped">
                    <thead>
                    <tr>
                        <th>
                            Категория
                        </th>
                        <th>
                            Наименование
                        </th>
                        <th>
                            Адрес
                        </th>
                        <th>
                            Телефон(ы)
                        </th>
                        <th>
                            График работы
                        </th>
                        <th>
                            ФИО руководителя
                        </th>
                        <th>
                            Есть договор
                        </th>
                        <th class="text-right">
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ( $managements as $management )
                        <tr>
                            <td>
                                {{ $management->getCategory() }}
                            </td>
                            <td>
                                {{ $management->name }}
                            </td>
                            <td>
                                {{ $management->address }}
                            </td>
                            <td>
                                {{ $management->getPhones() }}
                            </td>
                            <td>
                                {{ $management->schedule }}
                            </td>
                            <td>
                                {{ $management->director }}
                            </td>
                            <td>
                                @if ( $management->has_contract )
                                    <span class="label label-success">
                                        Да
                                    </span>
                                @else
                                    <span class="label label-danger">
                                        Нет
                                    </span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route( 'managements.edit', $management->id ) }}" class="btn btn-info">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                {{ $managements->render() }}

            @else
                @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
            @endif

        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.css" rel="stylesheet" type="text/css" />
@endsection