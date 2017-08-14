@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Роли', route( 'roles.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="portlet light">
        <div class="portlet-body">
            <div class="tab-content">

                {!! Form::model( $role, [ 'method' => 'put', 'route' => [ 'roles.update', $role->id ], 'id' => 'role-edit-form' ] ) !!}

                <div class="form-group">
                    <label class="control-label">Код</label>
                    {!! Form::text( 'code', \Input::old( 'code', $role->code ), [ 'class' => 'form-control', 'placeholder' => 'Код' ] ) !!}
                </div>

                <div class="form-group">
                    <label class="control-label">Наименование</label>
                    {!! Form::text( 'name', \Input::old( 'name', $role->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
                </div>

                <div class="form-group">
                    <label class="control-label">Guard</label>
                    {!! Form::select( 'guard_name', $guards, \Input::old( 'guard_name', $role->guard_name ), [ 'class' => 'form-control' ] ) !!}
                </div>

                <div class="caption caption-md">
                    <i class="icon-globe theme-font hide"></i>
                    <span class="caption-subject font-blue-madison bold uppercase">Права доступа</span>
                </div>

                @if ( $perms_tree )
                    <div id="tree" class="tree-demo jstree jstree-2 jstree-default jstree-checkbox-selection" role="tree" aria-multiselectable="true" tabindex="0" aria-busy="false" aria-selected="false">
                        <ul class="jstree-container-ul jstree-children jstree-wholerow-ul jstree-no-dots" role="group">
                            @include( 'admin.perms.tree', [ 'tree' => $perms_tree, 'role' => $role ] )
                        </ul>
                    </div>
                @endif

                <div id="perms-results"></div>

                <div class="margin-top-10">
                    {!! Form::submit( 'Редактировать', [ 'class' => 'btn green' ] ) !!}
                </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.min.css" rel="stylesheet" type="text/css" />
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