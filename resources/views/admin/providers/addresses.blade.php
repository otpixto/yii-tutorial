@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Поставщики', route( 'providers.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'admin.providers.edit' ) )

        <div class="well">
            <a href="{{ route( 'providers.edit', $provider->id ) }}">
                {{ $provider->name }}
            </a>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    Добавить Адреса
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::model( $provider, [ 'method' => 'put', 'route' => [ 'providers.buildings.add', $provider->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                <div class="form-group">
                    <div class="col-md-12">
                        {!! Form::select( 'buildings[]', [], null, [ 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'providers.buildings.search', $provider->id ), 'multiple' ] ) !!}
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

        <div class="panel panel-default">
            <div class="panel-body">

                {{ $providerBuildings->render() }}

                @if ( ! $providerBuildings->count() )
                    @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                @endif
                @foreach ( $providerBuildings as $r )
                    <div class="margin-bottom-5">
                        <button type="button" class="btn btn-xs btn-danger" data-delete="provider-building" data-building="{{ $r->id }}">
                            <i class="fa fa-remove"></i>
                        </button>
                        <a href="{{ route( 'buildings.edit', $r->id ) }}">
                            {{ $r->getAddress() }}
                        </a>
                    </div>
                @endforeach

                {{ $providerBuildings->render() }}

            </div>
        </div>

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection

@section( 'js' )
    <script type="text/javascript">

        $( document )

            .on( 'click', '[data-delete="provider-building"]', function ( e )
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
                                url: '{{ route( 'providers.buildings.del', $provider->id ) }}',
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