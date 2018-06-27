@extends( 'admin.users.template' )

@section( 'admin.content' )

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                Добавить УО
            </h3>
        </div>
        <div class="panel-body">
            {!! Form::model( $user, [ 'method' => 'put', 'route' => [ 'users.managements.add', $user->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
            <div class="form-group">
                <div class="col-md-12">
                    {!! Form::select( 'managements[]', [], null, [ 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'users.managements.search', $user->id ), 'multiple' ] ) !!}
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

            {{ $userManagements->render() }}

            @forelse ( $userManagements as $r )
                <div class="margin-bottom-5">
                    <button type="button" class="btn btn-xs btn-danger" data-delete="user-management" data-management="{{ $r->id }}">
                        <i class="fa fa-remove"></i>
                    </button>
                    <a href="{{ route( 'managements.edit', $r->id ) }}">
                        {{ $r->name }}
                    </a>
                </div>
            @empty
                @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
            @endforelse

            {{ $userManagements->render() }}

        </div>
    </div>

@endsection

@section( 'js' )
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

            })

            .on( 'click', '[data-delete="user-management"]', function ( e )
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
                                url: '{{ route( 'users.managements.del', $user->id ) }}',
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