@extends( 'admin.users.template' )

@section( 'admin.content' )

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                Роли
            </h3>
        </div>
        <div class="panel-body">
            <div class="mt-checkbox-list">
                @foreach ( $roles as $_role )
                    <label class="mt-checkbox mt-checkbox-outline">
                        {{ $_role->name }}
                        {!! Form::checkbox( 'roles[]', $_role->code, $user->hasRole( $_role->code ), [ 'class' => 'user_roles' ] ) !!}
                        <span></span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                Права доступа
            </h3>
        </div>
        <div class="panel-body">
            <div id="perms_tree" class="jstree jstree-2 jstree-default jstree-checkbox-selection" role="tree" aria-multiselectable="true" tabindex="0" aria-busy="false" aria-selected="false">
                @include( 'admin.perms.tree', [ 'perms_tree' => $perms_tree, 'user' => $user ] )
            </div>
        </div>
    </div>

@endsection

@section( 'admin.css' )
    <link href="/assets/global/plugins/jstree/dist/themes/default/style.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/jstree/dist/jstree.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        function permsTreeInit ()
        {

            $( '#perms_tree' )
                .on( 'changed.jstree', function ( e, data )
                {
                    if ( data.action == 'select_node' || data.action == 'deselect_node' )
                    {
                        $.ajax({
                            url: '{{ route( 'users.perms.update', $user->id ) }}',
                            method: 'put',
                            data: {
                                perms: data.selected
                            },
                            success: function ()
                            {

                            },
                            error: function ( e )
                            {
                                alert( e.statusText );
                            }
                        });
                    }
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

        };

        $( document )

            .ready( permsTreeInit )

            .on( 'change', '.user_roles', function ()
            {
                $( '#perms_tree' ).jstree( 'destroy' );
                $( '#perms_tree' ).loading();
                var roles = [];
                $( '.user_roles:checked' ).each( function ()
                {
                    roles.push( $( this ).val() );
                });
                $( '.user_roles' ).attr( 'disabled', 'disabled' );
                $.ajax({
                    url: '{{ route( 'users.roles.update', $user->id ) }}',
                    method: 'put',
                    data: {
                        roles: roles
                    },
                    success: function ( response )
                    {
                        $( '.user_roles' ).removeAttr( 'disabled' );
                        $( '#perms_tree' ).html( response );
                        permsTreeInit();
                    },
                    error: function ( e )
                    {
                        alert( e.statusText );
                    }
                });
            });

    </script>
@endsection