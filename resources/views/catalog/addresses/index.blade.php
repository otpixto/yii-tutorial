@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row margin-bottom-15">
        <div class="col-xs-12">
            <a href="{{ route( 'addresses.create' ) }}" class="btn btn-success">
                <i class="fa fa-plus"></i>
                Добавить здание
            </a>
        </div>
    </div>

    <div class="todo-ui">
        <div class="todo-sidebar">
            <div class="portlet light ">
                <div class="portlet-title">
                    <div class="caption" data-toggle="collapse" data-target="#search">
                        <span class="caption-subject font-green-sharp bold uppercase">ПОИСК</span>
                    </div>
                    <a href="{{ route( 'addresses.index' ) }}" class="btn btn-danger pull-right">сбросить</a>
                </div>
                <div class="portlet-body todo-project-list-content" id="search" style="height: auto;">
                    <div class="todo-project-list">
                        {!! Form::open( [ 'method' => 'get' ] ) !!}
                        <div class="row">
                            <div class="col-xs-12">
                                {!! Form::text( 'search', \Input::get( 'search' ), [ 'class' => 'form-control' ] ) !!}
                            </div>
                        </div>
                        <div class="row margin-top-10">
                            <div class="col-xs-12">
                                {!! Form::submit( 'Найти', [ 'class' => 'btn btn-info btn-block' ] ) !!}
                            </div>
                        </div>
                        {!! Form::hidden( 'management', \Input::get( 'management' ) ) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
        <!-- END TODO SIDEBAR -->

        <!-- BEGIN TODO CONTENT -->
        <div class="todo-content">
            <div class="portlet light ">
                <div class="portlet-body">

                    @if ( $addresses->count() )

                        {{ $addresses->render() }}

                        <table class="table table-hover table-striped">
                            <thead>
                            <tr>
                                <th>
                                    Адрес
                                </th>
                                <th>
                                    Здание
                                </th>
                                <th class="text-right">
                                    &nbsp;
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ( $addresses as $address )
                                <tr>
                                    <td>
                                        {{ $address->address }}
                                    </td>
                                    <td>
                                        {{ $address->home }}
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route( 'addresses.edit', $address->id ) }}" class="btn btn-info">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        {{ $addresses->render() }}

                    @else
                        @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                    @endif

                </div>
            </div>
        </div>
        <!-- END TODO CONTENT -->
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.css" rel="stylesheet" type="text/css" />
@endsection