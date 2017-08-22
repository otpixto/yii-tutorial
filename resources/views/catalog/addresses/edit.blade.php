@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'Здания', route( 'addresses.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::model( $address, [ 'method' => 'put', 'route' => [ 'addresses.update', $address->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

    <div class="form-group">

        <div class="col-xs-12">
            {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'name', \Input::old( 'name', $address->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
        </div>

    </div>

    <div class="form-group">
        <div class="col-xs-12">
            {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
        </div>
    </div>

    {!! Form::close() !!}

    <div class="row margin-top-15">
        <div class="col-md-12">
            <table class="table table-bordered table-hover table-striped">
                <thead>
                    <tr class="info">
                        <th class="text-right" width="35%">
                            Исполнитель
                        </th>
                        <th with="65%">
                           Типы обращений
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach ( $addressManagements as $management_id => $arr )
                    <tr>
                        <td class="text-right">
                            <a href="{{ route( 'managements.edit', $management_id ) }}">
                                {{ $arr[0]->name }}
                            </a>
                        </td>
                        <td>
                            <a href="#" data-toggle="#types-{{ $management_id }}">Скрыть\Показать ({{ $arr[1]->count() }})</a>
                            <ul class="list-group" style="display: none;" id="types-{{ $management_id }}">
                            @foreach ( $arr[1] as $type )
                                <li href="{{ route( 'types.edit', $type->id ) }}" class="list-group-item">
                                    {{ $type->name }}
                                    <a href="#" class="badge badge-danger pull-right" data-action="address-type-delete" data-type="{{ $type->id }}" data-managment="{{ $management_id }}" data-address="{{ $address->id }}">
                                        <i class="fa fa-remove"></i>
                                    </a>
                                </li>
                            @endforeach
                            </ul>
                            {!! Form::open( [ 'method' => 'post', 'url' => route( 'addresses.types.add' ) ] ) !!}
                            {!! Form::hidden( 'management_id', $management_id ) !!}
                            {!! Form::hidden( 'address_id', $address->id ) !!}
                            <div class="row">
                                <div class="col-md-12">
                                    {!! Form::select( 'types[]', $arr[2]->pluck( 'name', 'id' ), null, [ 'class' => 'form-control select2', 'id' => 'management-types', 'multiple' ] ) !!}
                                </div>
                            </div>
                            <div class="row margin-top-10">
                                <div class="col-md-12">
                                    <button id="add-management" class="btn btn-success">
                                        <i class="glyphicon glyphicon-ok"></i>
                                        Добавить Типы
                                    </button>
                                    <button id="del-management" class="btn btn-danger" data-managment="{{ $management_id }}" data-address="{{ $address->id }}">
                                        <i class="fa fa-remove"></i>
                                        Удалить ЭО
                                    </button>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {!! Form::open( [ 'method' => 'post', 'url' => route( 'addresses.managements.add' ) ] ) !!}
            {!! Form::hidden( 'address_id', $address->id ) !!}
            <div class="row">
                <div class="col-md-12">
                    {!! Form::select( 'managements[]', $allowedManagements, null, [ 'class' => 'form-control select2', 'id' => 'management-add', 'multiple' ] ) !!}
                </div>
            </div>
            <div class="row margin-top-10">
                <div class="col-md-12">
                    <button id="add-management" class="btn btn-success">
                        <i class="glyphicon glyphicon-plus"></i>
                        Добавить ЭО
                    </button>
                </div>
            </div>
            {!! Form::close() !!}

        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )
            .ready(function()
            {

                $( '.select2' ).select2();

            });

    </script>
@endsection