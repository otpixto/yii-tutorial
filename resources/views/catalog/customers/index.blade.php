@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'Заявители' ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row margin-bottom-15">
        <div class="col-xs-12">
            <a href="{{ route( 'customers.create' ) }}" class="btn btn-success">
                <i class="fa fa-plus"></i>
                Добавить заявителя
            </a>
        </div>
    </div>

    <div class="todo-ui">
        <div class="todo-sidebar">
            <div class="portlet light ">
                <div class="portlet-title">
                    <div class="caption">
                        <span class="caption-subject font-green-sharp bold uppercase">ПОИСК</span>
                    </div>
                    <a href="{{ route( 'customers.index' ) }}" class="btn btn-danger pull-right">сбросить</a>
                </div>
                <div class="portlet-body todo-project-list-content" style="height: auto;">
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

                    @if ( $customers->count() )

                        <table class="table table-hover table-striped">
                            <thead>
                            <tr>
                                <th>
                                    ФИО
                                </th>
                                <th>
                                    Телефон
                                </th>
                                <th>
                                    Доп. телефон
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
                                        {{ $customer->phone }}
                                    </td>
                                    <td>
                                        {{ $customer->phone2 }}
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route( 'customers.edit', $customer->id ) }}" class="btn btn-xs btn-info">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

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