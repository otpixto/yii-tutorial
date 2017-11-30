@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'Классификатор', route( 'types.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::model( $type, [ 'method' => 'put', 'route' => [ 'types.update', $type->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

    <div class="form-group">

        <div class="col-xs-6">
            {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'name', \Input::old( 'name', $type->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
        </div>

        <div class="col-xs-6">
            {!! Form::label( 'category_id', 'Категория обращений', [ 'class' => 'control-label' ] ) !!}
            {!! Form::select( 'category_id', $categories, \Input::old( 'category_id', $type->category_id ), [ 'class' => 'form-control select2', 'placeholder' => 'Категория обращений' ] ) !!}
        </div>

    </div>

    <div class="form-group">

        <div class="col-xs-6">
            {!! Form::label( 'period_acceptance', 'Период на принятие заявки в работу, час', [ 'class' => 'control-label' ] ) !!}
            {!! Form::number( 'period_acceptance', \Input::old( 'period_acceptance', $type->period_acceptance ), [ 'class' => 'form-control', 'placeholder' => 'Период на принятие заявки в работу, час', 'step' => 0.1, 'min' => 0 ] ) !!}
        </div>

        <div class="col-xs-6">
            {!! Form::label( 'period_execution', 'Период на исполнение, час', [ 'class' => 'control-label' ] ) !!}
            {!! Form::number( 'period_execution', \Input::old( 'period_execution', $type->period_execution ), [ 'class' => 'form-control', 'placeholder' => 'Период на исполнение, час', 'step' => 0.1, 'min' => 0 ] ) !!}
        </div>

    </div>

    <div class="form-group">

        <div class="col-xs-12">
            {!! Form::label( 'season', 'Сезонность устранения', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'season', \Input::old( 'season', $type->season ), [ 'class' => 'form-control', 'placeholder' => 'Сезонность устранения' ] ) !!}
        </div>

    </div>

    <div class="form-group">

        <div class="col-xs-4">
            {!! Form::label( 'need_act', 'Необходим акт', [ 'class' => 'control-label' ] ) !!}
            {!! Form::checkbox( 'need_act', 1, \Input::old( 'need_act', $type->need_act ), [ 'class' => 'form-control make-switch switch-large', 'placeholder' => 'Необходим акт', 'data-label-icon' => 'fa fa-fullscreen', 'data-on-text' => '<i class=\'fa fa-check\'></i>', 'data-off-text' => '<i class=\'fa fa-times\'></i>' ] ) !!}
        </div>

        <div class="col-xs-4">
            {!! Form::label( 'is_pay', 'Платно', [ 'class' => 'control-label' ] ) !!}
            {!! Form::checkbox( 'is_pay', 1, \Input::old( 'is_pay', $type->is_pay ), [ 'class' => 'form-control make-switch switch-large', 'placeholder' => 'Необходим акт', 'data-label-icon' => 'fa fa-fullscreen', 'data-on-text' => '<i class=\'fa fa-check\'></i>', 'data-off-text' => '<i class=\'fa fa-times\'></i>' ] ) !!}
        </div>

        <div class="col-xs-4">
            {!! Form::label( 'emergency', 'Авария', [ 'class' => 'control-label' ] ) !!}
            {!! Form::checkbox( 'emergency', 1, \Input::old( 'emergency', $type->emergency ), [ 'class' => 'form-control make-switch switch-large', 'placeholder' => 'Необходим акт', 'data-label-icon' => 'fa fa-fullscreen', 'data-on-text' => '<i class=\'fa fa-check\'></i>', 'data-off-text' => '<i class=\'fa fa-times\'></i>' ] ) !!}
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
                    <th width="50%">
                        ЭО
                        ({{ $typeManagements->count() }})
                    </th>
                    <th with="50%">
                        Адреса
                        ({{ $typeAddresses->count() }})
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        @if ( ! $typeManagements->count() )
                            @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                        @endif
                        @foreach ( $typeManagements as $r )
                            <div class="margin-bottom-5">
                                <a href="javascript:;" class="btn btn-xs btn-danger" data-delete="type-management" data-type="{{ $type->id }}" data-management="{{ $r->id }}">
                                    <i class="fa fa-remove"></i>
                                </a>
                                <a href="{{ route( 'managements.edit', $r->id ) }}">
                                    {{ $r->name }}
                                </a>
                            </div>
                        @endforeach
                    </td>
                    <td>
                        @if ( ! $typeAddresses->count() )
                            @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                        @endif
                        @foreach ( $typeAddresses as $r )
                            <div class="margin-bottom-5">
                                <a href="javascript:;" class="btn btn-xs btn-danger" data-delete="type-address" data-type="{{ $type->id }}" data-address="{{ $r->id }}">
                                    <i class="fa fa-remove"></i>
                                </a>
                                <a href="{{ route( 'addresses.edit', $r->id ) }}">
                                    {{ $r->name }}
                                </a>
                            </div>
                        @endforeach
                    </td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <td>
                        {!! Form::open( [ 'method' => 'post', 'url' => route( 'types.managements.add' ) ] ) !!}
                        {!! Form::hidden( 'type_id', $type->id ) !!}
                        <div class="row">
                            <div class="col-md-12">
                                {!! Form::select( 'managements[]', $allowedManagements, null, [ 'class' => 'form-control select2', 'id' => 'management-add', 'multiple' ] ) !!}
                            </div>
                        </div>
                        <div class="row margin-top-10">
                            <div class="col-md-12">
                                <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                    <input name="select_all_managements" id="select-all-managements" type="checkbox" value="1" />
                                    <span></span>
                                    Выбрать все
                                </label>
                                &nbsp;&nbsp;&nbsp;
                                <button id="add-management" class="btn btn-success">
                                    <i class="glyphicon glyphicon-plus"></i>
                                    Добавить ЭО
                                </button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </td>
                    <td>
                        {!! Form::open( [ 'method' => 'post', 'url' => route( 'types.addresses.add' ) ] ) !!}
                        {!! Form::hidden( 'type_id', $type->id ) !!}
                        <div class="row">
                            <div class="col-md-12">
                                {!! Form::select( 'addresses[]', $allowedAddresses, null, [ 'class' => 'form-control select2', 'id' => 'addresses-add', 'multiple' ] ) !!}
                            </div>
                        </div>
                        <div class="row margin-top-10">
                            <div class="col-md-12 text-right">
                                <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                    <input name="select_all_addresses" id="select-all-addresses" type="checkbox" value="1" />
                                    <span></span>
                                    Выбрать все
                                </label>
                                &nbsp;&nbsp;&nbsp;
                                <button id="add-management" class="btn btn-success">
                                    <i class="glyphicon glyphicon-plus"></i>
                                    Добавить Адрес
                                </button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </td>
                </tr>
                </tfoot>
            </table>

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

            })

            .on( 'click', '[data-delete="type-address"]', function ( e )
            {

                e.preventDefault();

                var type_id = $( this ).attr( 'data-type' );
                var address_id = $( this ).attr( 'data-address' );
                var obj = $( this ).closest( 'div' );

                bootbox.confirm({
                    message: 'Удалить привязку?',
                    size: 'small',
                    buttons: {
                        confirm: {
                            label: '<i class="fa fa-check"></i> Да',
                            className: 'btn-success'
                        },
                        cancel: {
                            label: '<i class="fa fa-times"></i> Нет',
                            className: 'btn-danger'
                        }
                    },
                    callback: function ( result )
                    {
                        if ( result )
                        {

                            obj.remove();

                            $.post( '{{ route( 'types.addresses.del' ) }}', {
                                type_id: type_id,
                                address_id: address_id
                            });

                        }
                    }
                });

            })

            .on( 'click', '[data-delete="type-management"]', function ( e )
            {

                e.preventDefault();

                var type_id = $( this ).attr( 'data-type' );
                var management_id = $( this ).attr( 'data-management' );
                var obj = $( this ).closest( 'div' );

                bootbox.confirm({
                    message: 'Удалить привязку?',
                    size: 'small',
                    buttons: {
                        confirm: {
                            label: '<i class="fa fa-check"></i> Да',
                            className: 'btn-success'
                        },
                        cancel: {
                            label: '<i class="fa fa-times"></i> Нет',
                            className: 'btn-danger'
                        }
                    },
                    callback: function ( result )
                    {
                        if ( result )
                        {

                            obj.remove();

                            $.post( '{{ route( 'types.managements.del' ) }}', {
                                type_id: type_id,
                                management_id: management_id
                            });

                        }
                    }
                });

            })

            .on( 'change', '#select-all-managements', function ()
            {
                if ( $( this ).is( ':checked' ) )
                {
                    $( '#management-add > option' ).prop( 'selected', 'selected' );
                    $( '#management-add' ).trigger( 'change' );
                }
                else
                {
                    $( '#management-add > option' ).removeAttr( 'selected' );
                    $( '#management-add' ).trigger( 'change' );
                }
            })

            .on( 'change', '#select-all-addresses', function ()
            {
                if ( $( this ).is( ':checked' ) )
                {
                    $( '#addresses-add > option' ).prop( 'selected', 'selected' );
                    $( '#addresses-add' ).trigger( 'change' );
                }
                else
                {
                    $( '#addresses-add > option' ).removeAttr( 'selected' );
                    $( '#addresses-add' ).trigger( 'change' );
                }
            });

    </script>
@endsection