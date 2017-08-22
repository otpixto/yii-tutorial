@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'Эксплуатирующие организации', route( 'managements.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::model( $management, [ 'method' => 'put', 'route' => [ 'managements.update', $management->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

    <div class="form-group">
        <div class="col-xs-4">
            {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'name', \Input::old( 'name', $management->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'phone', \Input::old( 'phone', $management->phone ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'phone2', 'Доп. телефон', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'phone2', \Input::old( 'phone2', $management->phone2 ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Доп. телефон' ] ) !!}
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-8">
            {!! Form::label( 'address', 'Адрес', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'address', \Input::old( 'address', $management->address ), [ 'class' => 'form-control', 'placeholder' => 'Адрес офиса' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'schedule', 'График работы', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'schedule', \Input::old( 'schedule', $management->schedule ), [ 'class' => 'form-control', 'placeholder' => 'График работы' ] ) !!}
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-4">
            {!! Form::label( 'director', 'ФИО руководителя', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'director', \Input::old( 'director', $management->director ), [ 'class' => 'form-control', 'placeholder' => 'ФИО руководителя' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'email', 'E-mail', [ 'class' => 'control-label' ] ) !!}
            {!! Form::email( 'email', \Input::old( 'email', $management->email ), [ 'class' => 'form-control', 'placeholder' => 'E-mail' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'site', 'Сайт', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'site', \Input::old( 'site', $management->site ), [ 'class' => 'form-control', 'placeholder' => 'Сайт' ] ) !!}
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-4">
            {!! Form::label( 'category', 'Категория ЭО', [ 'class' => 'control-label' ] ) !!}
            {!! Form::select( 'category', \App\Models\Management::$categories, \Input::old( 'category', $management->category ), [ 'class' => 'form-control select2', 'placeholder' => 'Категория ЭО' ] ) !!}
        </div>
        <div class="col-xs-4">
            {!! Form::label( 'services', 'Услуги', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'services', \Input::old( 'services', $management->services ), [ 'class' => 'form-control', 'placeholder' => 'Услуги' ] ) !!}
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
                        Адрес
                    </th>
                    <th with="65%">
                        Типы обращений
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach ( $addressManagements as $address_id => $arr )
                    <tr>
                        <td class="text-right">
                            <a href="{{ route( 'addresses.edit', $address_id ) }}">
                                {{ $arr[0]->name }}
                            </a>
                        </td>
                        <td>
                            <a href="#" data-toggle="#types-{{ $address_id }}">Скрыть\Показать ({{ $arr[1]->count() }})</a>
                            <ul class="list-group" style="display: none;" id="types-{{ $address_id }}">
                                @foreach ( $arr[1] as $type )
                                    <li href="{{ route( 'types.edit', $type->id ) }}" class="list-group-item">
                                        {{ $type->name }}
                                        <a href="#" class="badge badge-danger pull-right" data-action="address-type-delete" data-type="{{ $type->id }}" data-managment="{{ $management->id }}" data-address="{{ $address_id }}">
                                            <i class="fa fa-remove"></i>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            {!! Form::open( [ 'method' => 'post', 'url' => route( 'addresses.types.add' ) ] ) !!}
                            {!! Form::hidden( 'management_id', $management->id ) !!}
                            {!! Form::hidden( 'address_id', $address_id ) !!}
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
                                    <button type="button" id="address-type-delete" class="btn btn-danger" data-managment="{{ $management->id }}" data-address="{{ $address_id }}">
                                        <i class="fa fa-remove"></i>
                                        Удалить Здание
                                    </button>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {!! Form::open( [ 'method' => 'post', 'url' => route( 'managements.addresses.add' ) ] ) !!}
            {!! Form::hidden( 'management_id', $management->id ) !!}
            <div class="row">
                <div class="col-md-12">
                    {!! Form::select( 'addresses[]', $allowedAddresses, null, [ 'class' => 'form-control select2', 'multiple' ] ) !!}
                </div>
            </div>
            <div class="row margin-top-10">
                <div class="col-md-12">
                    <button id="add-management" class="btn btn-success">
                        <i class="glyphicon glyphicon-plus"></i>
                        Добавить Здания
                    </button>
                </div>
            </div>
            {!! Form::close() !!}

        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )
            .ready(function()
            {

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

                $( '.select2' ).select2();

                $( '[data-action="address-type-delete"]' ).click( function ( e )
                {

                    e.preventDefault();
                    if ( !confirm( 'Уверены, что хотите удалить?' ) ) return;

                    var data = {};

                    var management_id = $( this ).attr( 'data-management' );
                    var address_id = $( this ).attr( 'data-address' );
                    var type_id = $( this ).attr( 'data-type' );

                    if ( management_id )
                    {
                        data.management_id = management_id;
                    }

                    if ( address_id )
                    {
                        data.address_id = address_id;
                    }

                    if ( type_id )
                    {
                        data.type_id = type_id;
                    }

                    $.post( '{{ route( 'binds.delete' ) }}', data, function ( response )
                    {
                        console.log( response );
                    });

                });

            });

    </script>
@endsection