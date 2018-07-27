@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Здания', route( 'buildings.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.buildings.edit' ) )

        <div class="well">
            <a href="{{ route( 'buildings.edit', $building->id ) }}">
                {{ $building->name }}
            </a>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-plus"></i>
                    Добавить УО
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::model( $building, [ 'method' => 'put', 'route' => [ 'buildings.managements.add', $building->id ], 'class' => 'submit-loading' ] ) !!}
                <div class="row">
                    <div class="col-md-12">
                        <select class="mt-multiselect form-control" multiple="multiple" data-label="left" id="managements" name="managements[]">
                            @foreach ( $availableManagements as $management => $arr )
                                <optgroup label="{{ $management }}">
                                    @foreach ( $arr as $management_id => $management_name )
                                        <option value="{{ $management_id }}">
                                            {{ $management_name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row margin-top-15">
                    <div class="col-md-12">
                        {!! Form::submit( 'Добавить', [ 'class' => 'btn btn-success' ] ) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-search"></i>
                    Поиск
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::open( [ 'method' => 'get', 'route' => [ 'buildings.managements', $building->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                <div class="form-group">
                    <div class="col-md-12">
                        {!! Form::text( 'search', $search, [ 'class' => 'form-control' ] ) !!}
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        {!! Form::submit( 'Найти', [ 'class' => 'btn btn-success' ] ) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-body">

                {{ $buildingManagements->render() }}

                @if ( ! $buildingManagements->count() )
                    @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                @endif
                @foreach ( $buildingManagements as $r )
                    <div class="margin-bottom-5">
                        <button type="button" class="btn btn-xs btn-danger" data-delete="address-management" data-management="{{ $r->id }}">
                            <i class="fa fa-remove"></i>
                        </button>
                        <a href="{{ route( 'managements.edit', $r->id ) }}">
                            @if ( $r->parent )
                                <span class="text-muted">
                                    {{ $r->parent->name }}
                                </span>
                            @endif
                            {{ $r->name }}
                        </a>
                    </div>
                @endforeach

                {{ $buildingManagements->render() }}

                {!! Form::model( $building, [ 'method' => 'delete', 'route' => [ 'buildings.managements.empty', $building->id ], 'class' => 'form-horizontal submit-loading', 'data-confirm' => 'Вы уверены?' ] ) !!}
                <div class="form-group margin-top-15">
                    <div class="col-md-12">
                        {!! Form::submit( 'Удалить все', [ 'class' => 'btn btn-danger' ] ) !!}
                    </div>
                </div>
                {!! Form::close() !!}

            </div>
        </div>

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '.mt-multiselect' ).multiselect({
                    disableIfEmpty: true,
                    enableFiltering: true,
                    includeSelectAllOption: true,
                    enableCaseInsensitiveFiltering: true,
                    enableClickableOptGroups: true,
                    buttonWidth: '100%',
                    maxHeight: '300',
                    buttonClass: 'mt-multiselect btn btn-default',
                    numberDisplayed: 5,
                    nonSelectedText: '-',
                    nSelectedText: ' выбрано',
                    allSelectedText: 'Все',
                    selectAllText: 'Выбрать все',
                    selectAllValue: ''
                });

            })

            .on( 'click', '[data-delete="address-management"]', function ( e )
            {

                e.preventDefault();

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

                            obj.hide();

                            $.ajax({
                                url: '{{ route( 'buildings.managements.del', $building->id ) }}',
                                method: 'delete',
                                data: {
                                    management_id: management_id
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