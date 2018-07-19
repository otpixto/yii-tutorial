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
                    <i class="fa fa-plus"></i>
                    Добавить Классификатор
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::model( $provider, [ 'method' => 'put', 'route' => [ 'providers.types.add', $provider->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                <div class="form-group">
                    <div class="col-md-12">
                        {!! Form::select( 'types[]', $types, [], [ 'class' => 'form-control select2', 'multiple' ] ) !!}
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
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-search"></i>
                    Поиск
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::open( [ 'method' => 'get', 'route' => [ 'providers.types', $provider->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
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

                {{ $providerTypes->render() }}

                @if ( ! $providerTypes->count() )
                    @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                @endif
                @foreach ( $providerTypes as $r )
                    <div class="margin-bottom-5">
                        <button type="button" class="btn btn-xs btn-danger" data-delete="provider-type" data-type="{{ $r->id }}">
                            <i class="fa fa-remove"></i>
                        </button>
                        <a href="{{ route( 'managements.edit', $r->id ) }}">
                            {{ $r->name }}
                        </a>
                    </div>
                @endforeach

                {{ $providerTypes->render() }}

                {!! Form::model( $provider, [ 'method' => 'delete', 'route' => [ 'providers.types.empty', $provider->id ], 'class' => 'form-horizontal submit-loading', 'data-confirm' => 'Вы уверены?' ] ) !!}
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

            .on( 'click', '[data-delete="provider-type"]', function ( e )
            {

                e.preventDefault();

                var type_id = $( this ).attr( 'data-type' );
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
                                url: '{{ route( 'providers.types.del', $provider->id ) }}',
                                method: 'delete',
                                data: {
                                    type_id: type_id
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