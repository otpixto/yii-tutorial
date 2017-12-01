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

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Телефоны</h3>
        </div>
        <div class="panel-body">
            @foreach ( $region->phones as $phone )
                <div class="row margin-top-5 margin-bottom-5">
                    <div class="col-xs-12">
                        <button type="button" class="btn btn-xs btn-danger">
                            <i class="fa fa-remove"></i>
                        </button>
                        {{ $phone->phone }}
                    </div>
                </div>
            @endforeach
            <div class="form-group">
                <div class="col-xs-12">
                    {!! Form::label( 'phone', 'Добавить телефон', [ 'class' => 'control-label' ] ) !!}
                    {!! Form::text( 'phone', null, [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="form-group hidden-print">
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
                        Адреса
                        ({{ $region->addresses->count() }})
                    </th>
                    <th with="50%">
                        УО
                        ({{ $region->managements->count() }})
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        @if ( ! $region->addresses->count() )
                            @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                        @endif
                        @foreach ( $region->addresses as $r )
                            <div class="margin-bottom-5">
                                <a href="javascript:;" class="btn btn-xs btn-danger">
                                    <i class="fa fa-remove"></i>
                                </a>
                                <a href="{{ route( 'addresses.edit', $r->id ) }}">
                                    {{ $r->getAddress() }}
                                </a>
                            </div>
                        @endforeach
                    </td>
                    <td>
                        @if ( ! $region->managements->count() )
                            @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                        @endif
                        @foreach ( $region->managements as $r )
                            <div class="margin-bottom-5">
                                <a href="javascript:;" class="btn btn-xs btn-danger">
                                    <i class="fa fa-remove"></i>
                                </a>
                                <a href="{{ route( 'managements.edit', $r->id ) }}">
                                    {{ $r->name }}
                                </a>
                            </div>
                        @endforeach
                    </td>
                </tr>
                </tbody>
                {{--<tfoot>
                    <tr>
                        <td>
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
                                        Добавить Адреса
                                    </button>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </td>
                        <td>
                            {!! Form::open( [ 'method' => 'post', 'url' => route( 'addresses.types.add' ) ] ) !!}
                            {!! Form::hidden( 'address_id', $address->id ) !!}
                            <div class="row">
                                <div class="col-md-12">
                                    {!! Form::select( 'types[]', $allowedTypes, null, [ 'class' => 'form-control select2', 'id' => 'types-add', 'multiple' ] ) !!}
                                </div>
                            </div>
                            <div class="row margin-top-10">
                                <div class="col-md-12 text-right">
                                    <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                        <input name="select_all_types" id="select-all-types" type="checkbox" value="1" />
                                        <span></span>
                                        Выбрать все
                                    </label>
                                    &nbsp;&nbsp;&nbsp;
                                    <button id="add-management" class="btn btn-success">
                                        <i class="glyphicon glyphicon-plus"></i>
                                        Добавить Тип
                                    </button>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                </tfoot>--}}
            </table>

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

            .ready( function ()
            {

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

                $( '.select2' ).select2();

                $( '.select2-ajax' ).select2({
                    minimumInputLength: 3,
                    minimumResultsForSearch: 30,
                    ajax: {
                        delay: 450,
                        processResults: function ( data, page )
                        {
                            return {
                                results: data
                            };
                        }
                    }
                });

            });

    </script>
@endsection