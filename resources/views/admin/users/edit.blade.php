@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Пользователи', route( 'users.index' ) ],
        [ 'Редактировать пользователя "' . $user->email . '"' ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="portlet light">

        <div class="portlet-title tabbable-line">
            <div class="caption caption-md">
                <i class="icon-globe theme-font hide"></i>
                <span class="caption-subject font-blue-madison bold uppercase">Данные пользователя</span>
            </div>
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#personal" data-toggle="tab" aria-expanded="true">Персональная информация</a>
                </li>
                <li>
                    <a href="#password" data-toggle="tab">Сменить пароль</a>
                </li>
                <li>
                    <a href="#access" data-toggle="tab">Права доступа</a>
                </li>
            </ul>
        </div>

        <div class="portlet-body">
            <div class="tab-content">

            <!-- PERSONAL INFO TAB -->
                <div class="tab-pane active" id="personal">
                    {!! Form::model( $user, [ 'method' => 'put', 'route' => [ 'users.update', $user->id ] ] ) !!}
                    {!! Form::hidden( 'action', 'edit_personal' ) !!}
                    <div class="form-group">
                        <label class="control-label">Фамилия</label>
                        {!! Form::text( 'lastname', \Input::old( 'lastname', $user->lastname ), [ 'class' => 'form-control', 'placeholder' => 'Фамилия' ] ) !!}
                    </div>
                    <div class="form-group">
                        <label class="control-label">Имя</label>
                        {!! Form::text( 'firstname', \Input::old( 'firstname', $user->firstname ), [ 'class' => 'form-control', 'placeholder' => 'Имя' ] ) !!}
                    </div>
                    <div class="form-group">
                        <label class="control-label">Отчество</label>
                        {!! Form::text( 'middlename', \Input::old( 'middlename', $user->middlename ), [ 'class' => 'form-control', 'placeholder' => 'Отчество' ] ) !!}
                    </div>
                    <div class="form-group">
                        <label class="control-label">Телефон</label>
                        {!! Form::text( 'phone', \Input::old( 'phone', $user->phone ), [ 'class' => 'form-control', 'placeholder' => 'Телефон' ] ) !!}
                    </div>
                    <div class="margiv-top-10">
                        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
                <!-- END PERSONAL INFO TAB -->

                <!-- PASSWORD TAB -->
                <div class="tab-pane" id="password">
                    {!! Form::model( $user, [ 'method' => 'put', 'route' => [ 'users.update', $user->id ] ] ) !!}
                    {!! Form::hidden( 'action', 'change_password' ) !!}
                    <div class="form-group">
                        <label class="control-label">Пароль</label>
                        {!! Form::password( 'password', [ 'class' => 'form-control', 'placeholder' => 'Пароль' ] ) !!}
                    </div>
                    <div class="form-group">
                        <label class="control-label">Повторите пароль</label>
                        {!! Form::password( 'password_confirm', [ 'class' => 'form-control', 'placeholder' => 'Повторите пароль' ] ) !!}
                    </div>
                    <div class="margiv-top-10">
                        {!! Form::submit( 'Сменить пароль', [ 'class' => 'btn green' ] ) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
                <!-- END PASSWORD TAB -->

                <!-- PRIVACY SETTINGS TAB -->
                <div class="tab-pane" id="access">
                    {!! Form::model( $user, [ 'method' => 'put', 'route' => [ 'users.update', $user->id ] ] ) !!}
                    {!! Form::hidden( 'action', 'edit_access' ) !!}
                    <div class="caption caption-md">
                        <i class="icon-globe theme-font hide"></i>
                        <span class="caption-subject font-blue-madison bold uppercase">Выберите роли</span>
                    </div>

                    <div class="mt-checkbox-list">
                        @foreach ( $roles as $_role )
                            <label class="mt-checkbox mt-checkbox-outline">
                                {{ $_role->name }}
                                {!! Form::checkbox( 'roles[]', $_role->code, $user->hasRole( $_role->code ) ) !!}
                                <span></span>
                            </label>
                        @endforeach
                    </div>

                    <div class="caption caption-md">
                        <i class="icon-globe theme-font hide"></i>
                        <span class="caption-subject font-blue-madison bold uppercase">Выберите права</span>
                    </div>

                    @if ( $perms_tree )
                        <div id="tree" class="tree-demo jstree jstree-2 jstree-default jstree-checkbox-selection" role="tree" aria-multiselectable="true" tabindex="0" aria-busy="false" aria-selected="false">
                            <ul class="jstree-container-ul jstree-children jstree-wholerow-ul jstree-no-dots" role="group">
                                @include( 'admin.perms.tree', [ 'tree' => $perms_tree, 'user' => $user ] )
                            </ul>
                        </div>
                    @endif

                    <div id="perms-results"></div>

                    <!--end profile-settings-->
                    <div class="margin-top-10">
                        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
                <!-- END PRIVACY SETTINGS TAB -->

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