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
            {{ $provider->name }}
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-plus"></i>
                    Добавить УО
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::model( $provider, [ 'method' => 'put', 'route' => [ 'providers.managements.add', $provider->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                <div class="form-group">
                    <div class="col-md-12">
                        {!! Form::select( 'managements[]', [], null, [ 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'providers.managements.search', $provider->id ), 'multiple' ] ) !!}
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
                {!! Form::open( [ 'method' => 'get', 'route' => [ 'providers.managements', $provider->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
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

                {{ $providerManagements->render() }}

                @if ( ! $providerManagements->count() )
                    @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                @endif
                @foreach ( $providerManagements as $r )
                    <div class="margin-bottom-5">
                        <button type="button" class="btn btn-xs btn-danger" data-delete="provider-management" data-management="{{ $r->id }}">
                            <i class="fa fa-remove"></i>
                        </button>
                        <a href="{{ route( 'managements.edit', $r->id ) }}">
                            {{ $r->name }}
                        </a>
                    </div>
                @endforeach

                {{ $providerManagements->render() }}

                {!! Form::model( $provider, [ 'method' => 'delete', 'route' => [ 'providers.managements.empty', $provider->id ], 'class' => 'form-horizontal submit-loading', 'data-confirm' => 'Вы уверены?' ] ) !!}
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

            .on( 'click', '[data-delete="provider-management"]', function ( e )
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
                                url: '{{ route( 'providers.managements.del', $provider->id ) }}',
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