@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Справочники' ],
        [ 'Управляющие организации', route( 'managements.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.managements.edit' ) )

        <div class="well">
            <a href="{{ route( 'managements.edit', $management->id ) }}">
                {{ $management->name }}
            </a>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    Добавить Классификатор
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::model( $management, [ 'method' => 'put', 'route' => [ 'managements.types.add', $management->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                <div class="form-group">
                    <div class="col-md-12">
                        {!! Form::select( 'types[]', $allowedTypes, null, [ 'class' => 'form-control select2', 'multiple', 'id' => 'types' ] ) !!}
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                            <input name="select_all_types" id="select-all-types" type="checkbox" value="1" />
                            <span></span>
                            Выбрать все
                        </label>
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

                {{ $managementTypes->render() }}

                @if ( ! $managementTypes->count() )
                    @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
                @endif
                @foreach ( $managementTypes as $r )
                    <div class="margin-bottom-5">
                        <button type="button" class="btn btn-xs btn-danger" data-delete="management-type" data-type="{{ $r->id }}">
                            <i class="fa fa-remove"></i>
                        </button>
                        <a href="{{ route( 'types.edit', $r->id ) }}">
                            {{ $r->name }}
                        </a>
                    </div>
                @endforeach

                {{ $managementTypes->render() }}

            </div>
        </div>

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection

@section( 'js' )
    <script type="text/javascript">

        $( document )

            .on( 'click', '[data-delete="management-type"]', function ( e )
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
                                url: '{{ route( 'managements.types.del', $management->id ) }}',
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

            })

            .on( 'change', '#select-all-types', function ()
            {
                if ( $( this ).is( ':checked' ) )
                {
                    $( '#types > option' ).prop( 'selected', 'selected' );
                    $( '#types' ).trigger( 'change' );
                }
                else
                {
                    $( '#types > option' ).removeAttr( 'selected' );
                    $( '#types' ).trigger( 'change' );
                }
            });

    </script>
@endsection