@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Регионы', route( 'regions.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.regions.edit' ) )

        <div class="row">

            <div class="col-lg-6">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Редактрировать</h3>
                    </div>
                    <div class="panel-body">

                        {!! Form::model( $region, [ 'method' => 'put', 'route' => [ 'regions.update', $region->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                        <div class="form-group">

                            <div class="col-xs-6">
                                {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                                {!! Form::text( 'name', \Input::old( 'name', $region->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
                            </div>
                            <div class="col-xs-6">
                                {!! Form::label( 'domain', 'Домен', [ 'class' => 'control-label' ] ) !!}
                                {!! Form::text( 'domain', \Input::old( 'domain', $region->domain ), [ 'class' => 'form-control', 'placeholder' => 'Домен' ] ) !!}
                            </div>

                        </div>

                        <div class="form-group hidden-print">
                            <div class="col-xs-6">
                                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                            </div>
                            <div class="col-xs-6 text-right">
                                <a href="{{ route( 'regions.addresses', $region->id ) }}" class="btn btn-default btn-circle">
                                    Здания
                                    <span class="badge">{{ $regionAddressesCount }}</span>
                                </a>
                                <a href="{{ route( 'regions.managements', $region->id ) }}" class="btn btn-default btn-circle">
                                    УО
                                    <span class="badge">{{ $regionManagementsCount }}</span>
                                </a>
                            </div>
                        </div>

                        {!! Form::close() !!}

                    </div>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Телефоны</h3>
                    </div>
                    <div class="panel-body">
                        @foreach ( $region->phones as $phone )
                            <div class="row margin-top-5 margin-bottom-5">
                                <div class="col-xs-12">
                                    <button type="button" class="btn btn-xs btn-danger" data-delete="region-phone" data-phone="{{ $phone->id }}">
                                        <i class="fa fa-remove"></i>
                                    </button>
                                    {{ $phone->phone }}
                                </div>
                            </div>
                        @endforeach
                            {!! Form::model( $region, [ 'method' => 'put', 'route' => [ 'regions.phones.add', $region->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                        <div class="form-group">
                            <div class="col-xs-12">
                                {!! Form::label( 'phone', 'Добавить телефон', [ 'class' => 'control-label' ] ) !!}
                                {!! Form::text( 'phone', null, [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
                            </div>
                        </div>
                        <div class="form-group hidden-print">
                            <div class="col-xs-12">
                                {!! Form::submit( 'Добавить', [ 'class' => 'btn green' ] ) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

            </div>

        </div>

        <div class="row">
            <div class="col-xs-12">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">АИС ГЖИ</h3>
                    </div>
                    <div class="panel-body">

                        {!! Form::model( $region, [ 'method' => 'put', 'route' => [ 'regions.update', $region->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                        <div class="form-group">

                            <div class="col-xs-4">
                                {!! Form::label( 'guid', 'GUID', [ 'class' => 'control-label' ] ) !!}
                                {!! Form::text( 'guid', \Input::old( 'guid', $region->guid ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
                            </div>
                            <div class="col-xs-4">
                                {!! Form::label( 'username', 'Логин', [ 'class' => 'control-label' ] ) !!}
                                {!! Form::text( 'username', \Input::old( 'username', $region->username ), [ 'class' => 'form-control', 'placeholder' => 'Логин' ] ) !!}
                            </div>
                            <div class="col-xs-4">
                                {!! Form::label( 'password', 'Пароль', [ 'class' => 'control-label' ] ) !!}
                                {!! Form::text( 'password', \Input::old( 'password', $region->password ), [ 'class' => 'form-control', 'placeholder' => 'Пароль' ] ) !!}
                            </div>

                        </div>

                        <div class="form-group hidden-print">
                            <div class="col-xs-12">
                                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                            </div>
                        </div>

                        {!! Form::close() !!}

                    </div>

                </div>

            </div>
        </div>

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'js' )
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

            })

            .on( 'click', '[data-delete="region-phone"]', function ( e )
            {

                e.preventDefault();

                var phone_id = $( this ).attr( 'data-phone' );
                var obj = $( this ).closest( '.row' );

                bootbox.confirm({
                    message: 'Удалить телефон?',
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

                            obj.hide();

                            $.ajax({
                                url: '{{ route( 'regions.phones.del', $region->id ) }}',
                                method: 'delete',
                                data: {
                                    phone_id: phone_id
                                },
                                success: function ()
                                {
                                    obj.remove();
                                },
                                error: function ( e )
                                {
                                    obj.show();
                                    alert( e.statusText );
                                }
                            });

                        }
                    }
                });

            });

    </script>
@endsection