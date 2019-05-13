@extends( 'admin.users.template' )

@section( 'users.content' )

    <div class="panel panel-default">
        <div class="panel-body">

            {{ $userExecutors->render() }}

            @forelse ( $userExecutors as $r )
                <div class="margin-bottom-5">
                    <button type="button" class="btn btn-xs btn-danger" data-delete="user-executor" data-executor="{{ $r->id }}">
                        <i class="fa fa-remove"></i>
                    </button>
                    <a href="{{ route( 'executors.edit', $r->id ) }}">
                        {{ $r->name }}
                    </a>
                </div>
            @empty
                @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
            @endforelse

            {{ $userExecutors->render() }}

        </div>
    </div>

@endsection

@section( 'js' )
    <script type="text/javascript">

        $( document )

            .on( 'click', '[data-delete="user-executor"]', function ( e )
            {

                e.preventDefault();

                var executor_id = $( this ).attr( 'data-executor' );
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
                                url: '{{ route( 'users.executors.del', $user->id ) }}',
                                method: 'delete',
                                data: {
                                    executor_id: executor_id
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