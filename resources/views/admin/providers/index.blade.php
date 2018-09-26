@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin )

        <div class="row margin-bottom-15 hidden-print">
            <div class="col-xs-12">
                <a href="{{ route( 'providers.create' ) }}" class="btn btn-success btn-lg">
                    <i class="fa fa-plus"></i>
                    Добавить поставщика
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

                @if ( $providers->count() )

                    {{ $providers->render() }}

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
                                Здания
                            </th>
                            <th class="text-center">
                                УО
                            </th>
                            <th class="text-center">
                                Классификатор
                            </th>
                            <th class="text-right">
                                &nbsp;
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ( $providers as $provider )
                            <tr>
                                <td>
                                    {{ $provider->name }}
                                </td>
                                <td>
                                    <a href="//{{ $provider->domain }}">
                                        {{ $provider->domain }}
                                    </a>
                                </td>
                                <td>
                                    {{ $provider->phones->implode( 'phone', ', ' ) }}
                                </td>
                                <td class="text-center">
                                    <a href="//{{ $provider->domain }}/{{ route( 'buildings.index', null, false ) }}" class="badge badge-{{ $provider->buildings()->count() ? 'info' : 'default' }} bold">
                                        {{ $provider->buildings()->count() }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a href="//{{ $provider->domain }}/{{ route( 'managements.index', null, false ) }}" class="badge badge-{{ $provider->managements()->count() ? 'info' : 'default' }} bold">
                                        {{ $provider->managements()->count() }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a href="//{{ $provider->domain }}/{{ route( 'types.index', null, false ) }}" class="badge badge-{{ $provider->types()->count() ? 'info' : 'default' }} bold">
                                        {{ $provider->types()->count() }}
                                    </a>
                                </td>
                                <td class="text-right">
                                    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.providers.edit' ) )
                                        <a href="{{ route( 'providers.edit', $provider->id ) }}" class="btn btn-info">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {{ $providers->render() }}

                @else
                    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                @endif

            </div>
        </div>

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.css" rel="stylesheet" type="text/css" />
@endsection