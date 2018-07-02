@extends( 'admin.users.template' )

@section( 'admin.content' )

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                Добавить Регион
            </h3>
        </div>
        <div class="panel-body">
            {!! Form::model( $user, [ 'method' => 'put', 'route' => [ 'users.regions.add', $user->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
            <div class="form-group">
                <div class="col-md-12">
                    {!! Form::select( 'regions[]', $regions, null, [ 'class' => 'form-control select2', 'id' => 'regions', 'multiple' ] ) !!}
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

            {{ $userRegions->render() }}

            @forelse ( $userRegions as $r )
                <div class="margin-bottom-5">
                    <button type="button" class="btn btn-xs btn-danger" data-delete="user-region" data-region="{{ $r->id }}">
                        <i class="fa fa-remove"></i>
                    </button>
                    <a href="{{ route( 'regions.edit', $r->id ) }}">
                        {{ $r->name }}
                    </a>
                </div>
            @empty
                @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
            @endforelse

            {{ $userRegions->render() }}

        </div>
    </div>

@endsection

@section( 'js' )
    <script type="text/javascript">

        $( document )

            .on( 'click', '[data-delete="user-region"]', function ( e )
            {

                e.preventDefault();

                var region_id = $( this ).attr( 'data-region' );
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
                                url: '{{ route( 'users.regions.del', $user->id ) }}',
                                method: 'delete',
                                data: {
                                    region_id: region_id
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