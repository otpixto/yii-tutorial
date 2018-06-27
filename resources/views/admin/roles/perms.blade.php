@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Роли', route( 'roles.index' ) ],
        [ $role->name, route( 'roles.edit', $role->id ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.roles.edit' ) )

        <div class="well">
            <a href="{{ route( 'roles.edit', $role->id ) }}">
                {{ $role->code }}
                ({{ $role->name }})
            </a>
        </div>

        {!! Form::model( $role, [ 'method' => 'put', 'id' => 'role-edit-form', 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">
            <div class="col-xs-6">
                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
            </div>
            <div class="col-xs-6 text-right">
                <a href="{{ route( 'roles.edit', $role->id ) }}" class="btn btn-default btn-circle">
                    Редактировать роль
                </a>
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-12">
                <div id="perms_tree" class="tree-demo jstree jstree-2 jstree-default jstree-checkbox-selection" role="tree" aria-multiselectable="true" tabindex="0" aria-busy="false" aria-selected="false">
                    @if ( $perms_tree )
                        @include( 'admin.perms.tree', [ 'perms_tree' => $perms_tree, 'role' => $role ] )
                    @endif
                </div>
            </div>
        </div>

        <div id="perms-results"></div>

        <div class="form-group">
            <div class="col-xs-6">
                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
            </div>
            <div class="col-xs-6 text-right">
                <a href="{{ route( 'roles.edit', $role->id ) }}" class="btn btn-default btn-circle">
                    Редактировать роль
                </a>
            </div>
        </div>

        {!! Form::close() !!}

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/jstree/dist/themes/default/style.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/jstree/dist/jstree.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                @if ( $perms_tree )

                    $( '#perms_tree' )

                        .on( 'changed.jstree', function ( e, data )
                        {
                            $( '#perms-results' ).empty();
                            $.each( data.selected, function ( i, code )
                            {
                                $( '#perms-results' ).append(
                                    $( '<input type="hidden" name="perms[]">' ).val( code )
                                );
                            });
                        })

                        .jstree(
                            {
                                'plugins': [
                                    'wholerow',
                                    'checkbox'
                                ],
                                "core": {
                                    "themes":
                                    {
                                        "icons":false
                                    }
                                }
                            });

                @endif

            });

    </script>
@endsection