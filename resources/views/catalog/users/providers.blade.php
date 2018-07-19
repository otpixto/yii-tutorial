@extends( 'catalog.users.template' )

@section( 'users.content' )

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                Добавить к Поставщику
            </h3>
        </div>
        <div class="panel-body">
            {!! Form::model( $user, [ 'method' => 'put', 'route' => [ 'users.providers.add', $user->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
            <div class="form-group">
                <div class="col-md-12">
                    {!! Form::select( 'providers[]', $providers, null, [ 'class' => 'form-control select2', 'id' => 'providers', 'multiple' ] ) !!}
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

            {{ $userProviders->render() }}

            @forelse ( $userProviders as $r )
                <div class="margin-bottom-5">
                    <button type="button" class="btn btn-xs btn-danger" data-delete="user-provider" data-provider="{{ $r->id }}">
                        <i class="fa fa-remove"></i>
                    </button>
                    <a href="{{ route( 'providers.edit', $r->id ) }}">
                        {{ $r->name }}
                    </a>
                </div>
            @empty
                @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
            @endforelse

            {{ $userProviders->render() }}

        </div>
    </div>

@endsection

@section( 'js' )
    <script type="text/javascript">

        $( document )

            .on( 'click', '[data-delete="user-provider"]', function ( e )
            {

                e.preventDefault();

                var provider_id = $( this ).attr( 'data-provider' );
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
                                url: '{{ route( 'users.providers.del', $user->id ) }}',
                                method: 'delete',
                                data: {
                                    provider_id: provider_id
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