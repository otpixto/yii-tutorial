@extends( 'admin.users.template' )

@section( 'users.content' )

    @if ( \Auth::user()->can( 'admin.users.types' ) )
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-plus"></i>
                    Добавить Классификатор
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::model( $user, [ 'method' => 'put', 'route' => [ 'users.types.add', $user->id ], 'class' => 'submit-loading' ] ) !!}
                <div class="row">
                    <div class="col-md-6">
                        <select class="form-control" id="category_id" name="category_id">
                            <option value="">
                                -
                            </option>
                            @foreach ( $availableCategories as $category )
                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <select class="form-control hidden" multiple id="types" name="types[]">
                            {{--@foreach ( $availableTypes as $type )
                                <option value="{{ $type->id }}">
                                    {{ $type->name }}
                                </option>
                            @endforeach--}}
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
    @endif

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-search"></i>
                Поиск
            </h3>
        </div>
        <div class="panel-body">
            {!! Form::open( [ 'method' => 'get', 'route' => [ 'users.types', $user->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
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

            {{ $userTypes->render() }}

            @forelse ( $userTypes as $r )
                <div class="margin-bottom-5">
                    @if ( \Auth::user()->can( 'admin.users.types' ) )
                        <button type="button" class="btn btn-xs btn-danger" data-delete="user-type" data-type="{{ $r->id }}">
                            <i class="fa fa-remove"></i>
                        </button>
                    @endif
                    <a href="{{ route( 'types.edit', $r->id ) }}">
                        {{ $r->name }}
                    </a>
                </div>
            @empty
                @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
            @endforelse

            {{ $userTypes->render() }}

            {!! Form::model( $user, [ 'method' => 'delete', 'route' => [ 'users.types.empty', $user->id ], 'class' => 'form-horizontal submit-loading', 'data-confirm' => 'Вы уверены?' ] ) !!}
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

                $( '#category_id' )
                    .select2()
                    .on( 'select2:select', function ( e )
                    {
                        var data = e.params.data;
                        $.post( '{{ route( 'users.types', $user->id ) }}', {
                            parent_id: data.id
                        }, function ( response )
                        {
                            $( '#types' ).empty();
                            $.each( response, function ( i, item )
                            {
                                $( '#types' ).append(
                                    $( '<option>' ).val( item.id ).text( item.text )
                                );
                            });
                            $( '#types' ).removeClass( 'hidden' );
                            $( '#types' ).multiselect( 'destroy' );
                            $( '#types' ).multiselect({
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
                        });
                    });

            })

            .on( 'click', '[data-delete="user-type"]', function ( e )
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
                                url: '{{ route( 'users.types.del', $user->id ) }}',
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