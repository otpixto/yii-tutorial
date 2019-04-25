@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Группы', route( 'buildings_groups.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.groups.edit' ) )

        <div class="well">
            <a href="{{ route( 'buildings_groups.edit', $group->id ) }}">
                {{ $group->name }}
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
                        {!! Form::model( $group, [ 'method' => 'put', 'route' => [ 'buildings_groups.segments.add', $group->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                        <div class="form-group">
                            <div class="col-md-12">
                                <div id="segment_id" data-name="segments[]"></div>
                            </div>
                        </div>
                        <div class="form-group">
							{!! Form::label( 'type_id', 'Тип здания', [ 'class' => 'control-label col-md-4' ] ) !!}
							<div class="col-md-4">
								{!! Form::select( 'type_id', $buildingTypes, '', [ 'class' => 'form-control select2', 'id' => 'type_id', 'placeholder' => 'ВСЕ' ] ) !!}
							</div>
                            <div class="col-md-4">
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
                        {!! Form::model( $group, [ 'method' => 'put', 'route' => [ 'buildings_groups.buildings.add', $group->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                        <div class="form-group">
                            <div class="col-md-12">
                                {!! Form::select( 'buildings[]', [], null, [ 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'buildings_groups.buildings.search', $group->id ), 'multiple' ] ) !!}
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
                {!! Form::open( [ 'method' => 'get', 'route' => [ 'buildings_groups.buildings', $group->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
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

                {{ $groupBuildings->render() }}

                @if ( ! $groupBuildings->count() )
                    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                @endif
                @foreach ( $groupBuildings as $r )
                    <div class="margin-bottom-5">
                        <button type="button" class="btn btn-xs btn-danger" data-delete="{{ $r->id }}">
                            <i class="fa fa-remove"></i>
                        </button>
                        <a href="{{ route( 'buildings.edit', $r->id ) }}">
                            {{ $r->getAddress( true ) }}
                        </a>
                    </div>
                @endforeach

                {{ $groupBuildings->render() }}

                {!! Form::model( $group, [ 'method' => 'delete', 'route' => [ 'buildings_groups.buildings.empty', $group->id ], 'class' => 'form-horizontal submit-loading', 'data-confirm' => 'Вы уверены?' ] ) !!}
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

                $( '#segment_id' ).selectSegments();

            })

            .on( 'click', '[data-delete]', function ( e )
            {

                e.preventDefault();

                var building_id = $( this ).attr( 'data-delete' );
                var obj = $( this ).closest( 'div' );

                bootbox.confirm({
                    message: 'Удалить адрес из группы?',
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
                                url: '{{ route( 'buildings_groups.buildings.del', $group->id ) }}',
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