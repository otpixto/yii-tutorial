@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Классификатор', route( 'types.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

        <div class="well">
            <a href="{{ route( 'types.edit', $type->id ) }}">
                {{ $type->name }}
            </a>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-plus"></i>
                    Добавить пользователя
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::model( $type, [ 'method' => 'put', 'route' => [ 'types.users.add', $type->id ], 'class' => 'submit-loading' ] ) !!}
                <div class="row">
                    <div class="col-md-12">
                        <select class="mt-multiselect form-control" multiple="multiple" data-label="left" id="users" name="users[]">
                            @foreach ( $availableUsers as $id => $userName )
                                        <option value="{{ $id }}">
                                            {{ $userName }}
                                        </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row margin-top-15">
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
                    Поиск по фамилии
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::open( [ 'method' => 'get', 'route' => [ 'types.users', $type->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
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

                {{ $typeUsers->render() }}

                @if ( ! $typeUsers->count() )
                    @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                @endif
                @foreach ( $typeUsers as $r )
                    <div class="margin-bottom-5">
                        <button type="button" class="btn btn-xs btn-danger" data-delete="type-user-del" data-type="{{ $type->id }}" data-user-id="{{ $r->id }}">
                            <i class="fa fa-remove"></i>
                        </button>
                        <a href="{{ route( 'users.edit', $r->id ) }}">
                            {{ $r->getName() }}
                        </a>
                    </div>
                @endforeach

                {{ $typeUsers->render() }}

                {!! Form::model( $type, [ 'method' => 'delete', 'route' => [ 'types.users.empty', $type->id ], 'class' => 'form-horizontal submit-loading', 'data-confirm' => 'Вы уверены?' ] ) !!}
                <div class="form-group margin-top-15">
                    <div class="col-md-12">
                        {!! Form::submit( 'Удалить все', [ 'class' => 'btn btn-danger' ] ) !!}
                    </div>
                </div>
                {!! Form::close() !!}

            </div>
        </div>

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '.mt-multiselect' ).multiselect({
                    disableIfEmpty: true,
                    enableFiltering: true,
                    includeSelectAllOption: true,
                    enableCaseInsensitiveFiltering: true,
                    enableClickableOptGroups: true,
                    buttonWidth: '100%',
                    maxHeight: '300',
                    buttonClass: 'mt-multiselect btn btn-default',
                    numberDisplayed: 5,
                    nonSelectedText: '-',
                    nSelectedText: ' выбрано',
                    allSelectedText: 'Все',
                    selectAllText: 'Выбрать все',
                    selectAllValue: ''
                });

            })

            .on( 'click', '[data-delete="type-user-del"]', function ( e )
            {

                e.preventDefault();

                var user_id = $( this ).attr( 'data-user-id' );
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
                                url: '{{ route( 'types.users.del', $type->id ) }}',
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