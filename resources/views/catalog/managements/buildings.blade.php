@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Управляющие организации', route( 'managements.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.managements.edit' ) )

        <div class="well">
            <a href="{{ route( 'managements.edit', $management->id ) }}">
                @if ( $management->parent )
                    <div class="text-muted">
                        {{ $management->parent->name }}
                    </div>
                @endif
                {{ $management->name }}
            </a>
        </div>

        <div class="row">
            <div class="col-md-6">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-plus"></i>
                            Добавить Здания из сегмента
                        </h3>
                    </div>
                    <div class="panel-body">
                        {!! Form::model( $management, [ 'method' => 'put', 'route' => [ 'managements.segments.add', $management->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                        <div class="form-group">
                            <div class="col-md-12">
                                {!! Form::text( 'segment_id', 'Нажмите, чтобы выбрать', [ 'class' => 'form-control', 'id' => 'segment_id' ] ) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                {!! Form::submit( 'Добавить', [ 'class' => 'btn btn-success' ] ) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

            </div>
            <div class="col-md-6">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-plus"></i>
                            Добавить Здания
                        </h3>
                    </div>
                    <div class="panel-body">
                        {!! Form::model( $management, [ 'method' => 'put', 'route' => [ 'managements.buildings.add', $management->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                        <div class="form-group">
                            <div class="col-md-12">
                                {!! Form::select( 'buildings[]', [], null, [ 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'managements.buildings.search', $management->id ), 'multiple' ] ) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                {!! Form::submit( 'Добавить', [ 'class' => 'btn btn-success' ] ) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

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
                {!! Form::open( [ 'method' => 'get', 'route' => [ 'managements.buildings', $management->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
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

                {{ $managementBuildings->render() }}

                @if ( ! $managementBuildings->count() )
                    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                @endif
                @foreach ( $managementBuildings as $r )
                    <div class="margin-bottom-5">
                        <button type="button" class="btn btn-xs btn-danger" data-delete="management-building" data-building="{{ $r->id }}">
                            <i class="fa fa-remove"></i>
                        </button>
                        <a href="{{ route( 'buildings.edit', $r->id ) }}">
                            {{ $r->getAddress() }}
                        </a>
                    </div>
                @endforeach

                {{ $managementBuildings->render() }}

                {!! Form::model( $management, [ 'method' => 'delete', 'route' => [ 'managements.buildings.empty', $management->id ], 'class' => 'form-horizontal submit-loading', 'data-confirm' => 'Вы уверены?' ] ) !!}
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

@section( 'js' )
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '#segment_id' ).selectSegment();

            })

            .on( 'click', '[data-delete="management-building"]', function ( e )
            {

                e.preventDefault();

                var building_id = $( this ).attr( 'data-building' );
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
                                url: '{{ route( 'managements.buildings.del', $management->id ) }}',
                                method: 'delete',
                                data: {
                                    building_id: building_id
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