@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Права доступа', route( 'perms.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin )

        <div class="well">
            <a href="{{ route( 'perms.edit', $perm->id ) }}">
                {{ $perm->code }}
                ({{ $perm->name }})
            </a>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    Добавить Пользователю
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::model( $perm, [ 'url' => route( 'perms.users.add', $perm->id ), 'method' => 'put', 'class' => 'form-horizontal submit-loading' ] ) !!}
                <div class="form-group">
                    <div class="col-md-12">
                        {!! Form::select( 'users[]', [], null, [ 'class' => 'form-control select2-ajax', 'data-ajax--url' => route( 'perms.users.search', $perm->id ), 'multiple' ] ) !!}
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

                {{ $permUsers->render() }}

                @if ( ! $permUsers->count() )
                    @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                @endif
                @foreach ( $permUsers as $r )
                    <div class="margin-bottom-5">
                        <button type="button" class="btn btn-xs btn-danger" data-delete="perm-user" data-id="{{ $r->id }}">
                            <i class="fa fa-remove"></i>
                        </button>
                        <a href="{{ route( 'users.edit', $r->id ) }}">
                            {{ $r->getName() }}
                        </a>
                    </div>
                @endforeach

                {{ $permUsers->render() }}

            </div>
        </div>

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection


@section( 'js' )
    <script type="text/javascript">

        $( document )

            .ready(function()
            {

            })

            .on( 'click', '[data-delete="perm-user"]', function ( e )
            {

                e.preventDefault();

                var user_id = $( this ).attr( 'data-id' );
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
                                url: '{{ route( 'perms.users.del', $perm->id ) }}',
                                method: 'delete',
                                data: {
                                    user_id: user_id
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