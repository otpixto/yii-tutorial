@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row margin-bottom-15 hidden-print">
        <div class="col-xs-12">
            <a href="{{ route( 'regions.create' ) }}" class="btn btn-success btn-lg">
                <i class="fa fa-plus"></i>
                Добавить регион
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

            @if ( $regions->count() )

                {{ $regions->render() }}

                <table class="table table-hover table-striped">
                    <thead>
                    <tr>
                        <th>
                            Наименование
                        </th>
                        <th>
                            Домен
                        </th>
                        <th>
                            Телефоны
                        </th>
                        <th class="text-center">
                            Адреса
                        </th>
                        <th class="text-center">
                            УО
                        </th>
                        <th class="text-right">
                            &nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ( $regions as $region )
                        <tr>
                            <td>
                                {{ $region->name }}
                            </td>
                            <td>
                                {{ $region->domain }}
                            </td>
                            <td>
                                {{ $region->phones->implode( 'phone', ', ' ) }}
                            </td>
                            <td>
                                {{ $region->addresses->count() }}
                            </td>
                            <td>
                                {{ $region->managements->count() }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route( 'regions.edit', $region->id ) }}" class="btn btn-info">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                {{ $regions->render() }}

            @else
                @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
            @endif

        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.css" rel="stylesheet" type="text/css" />
@endsection