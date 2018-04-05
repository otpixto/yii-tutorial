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

    {!! Form::model( $role, [ 'method' => 'put', 'id' => 'role-edit-form' ] ) !!}

    <div class="margin-top-10">
        <a href="{{ route( 'roles.edit', $role->id ) }}" class="btn btn-info">
            Редактировать
        </a>
        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
    </div>

    <div id="tree" class="tree-demo jstree jstree-2 jstree-default jstree-checkbox-selection margin-top-10" role="tree" aria-multiselectable="true" tabindex="0" aria-busy="false" aria-selected="false">
        <ul class="jstree-container-ul jstree-children jstree-wholerow-ul jstree-no-dots" role="group">
            @if ( $perms_tree )
                @include( 'admin.perms.tree', [ 'tree' => $perms_tree, 'role' => $role ] )
            @endif
        </ul>
    </div>

    <div id="perms-results"></div>

    <div class="margin-top-10">
        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
    </div>

    {!! Form::close() !!}

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

                    $( '#tree' )

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