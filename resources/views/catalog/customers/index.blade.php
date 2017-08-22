@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row margin-bottom-15 hidden-print">
        <div class="col-xs-6">
            <a href="{{ route( 'customers.create' ) }}" class="btn btn-success btn-lg">
                <i class="fa fa-plus"></i>
                Добавить заявителя
            </a>
        </div>
        <div class="col-xs-6 text-right">
            <a href="?export=1&{{ Request::getQueryString() }}" class="btn btn-default btn-lg">
                <i class="fa fa-download"></i>
                Выгрузить в Excel
            </a>
        </div>
    </div>

    <div class="row hidden-print">
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

            @if ( $customers->count() )

                {{ $customers->render() }}

                <table class="table table-hover table-striped">
                    <thead>
                    <tr>
                        <th>
                            ФИО
                        </th>
                        <th>
                            Телефон(ы)
                        </th>
                        <th>
                            Адрес
                        </th>
                        <th>
                            E-mail
                        </th>
                        <th class="text-right">
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ( $customers as $customer )
                        <tr>
                            <td>
                                {{ $customer->getName() }}
                            </td>
                            <td>
                                {{ $customer->getPhones() }}
                            </td>
                            <td>
                                {{ $customer->getAddress() }}
                            </td>
                            <td>
                                {{ $customer->email }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route( 'customers.edit', $customer->id ) }}" class="btn btn-info">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                {{ $customers->render() }}

            @else
                @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
            @endif

        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.css" rel="stylesheet" type="text/css" />
@endsection