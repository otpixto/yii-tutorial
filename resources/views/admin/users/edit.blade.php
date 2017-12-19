@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Пользователи', route( 'users.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin )
        <div class="row">
            <div class="col-xs-12 text-right">
                <a href="{{ route( 'loginas', $user->id ) }}" class="btn btn-default">
                    Залогиниться под этим пользователем
                </a>
            </div>
        </div>
    @endif

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
                    <a href="#binds" data-toggle="tab">Привязки УО</a>
                </li>
                <li>
                    <a href="#password" data-toggle="tab">Сменить пароль</a>
                </li>
                <li>
                    <a href="#access" data-toggle="tab">Права доступа</a>
                </li>
                @if ( \Auth::user()->can( 'admin.logs' ) )
                    <li>
                        <a href="#logs" data-toggle="tab">Логи</a>
                    </li>
                @endif
            </ul>
        </div>

        <div class="portlet-body">

            <div class="tab-content">

                <!-- PERSONAL INFO TAB -->
                <div class="tab-pane active" id="personal">
                    {!! Form::model( $user, [ 'method' => 'put', 'route' => [ 'users.update', $user->id ] ] ) !!}
                    {!! Form::hidden( 'action', 'edit_personal' ) !!}
                    <div class="form-group">
                        {!! Form::label( 'regions', 'Регион', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'regions[]', $regions->pluck( 'name', 'id' ), \Input::old( 'regions', $user->regions->pluck( 'id' ) ), [ 'class' => 'form-control select2', 'data-placeholder' => 'Регион', 'multiple' ] ) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label( 'lastname', 'Фамилия', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'lastname', \Input::old( 'lastname', $user->lastname ), [ 'class' => 'form-control', 'placeholder' => 'Фамилия' ] ) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label( 'firstname', 'Имя', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'firstname', \Input::old( 'firstname', $user->firstname ), [ 'class' => 'form-control', 'placeholder' => 'Имя' ] ) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label( 'middlename', 'Отчество', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'middlename', \Input::old( 'middlename', $user->middlename ), [ 'class' => 'form-control', 'placeholder' => 'Отчество' ] ) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'phone', \Input::old( 'phone', $user->phone ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label( 'company', 'Компания', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'company', \Input::old( 'company', $user->company ), [ 'class' => 'form-control', 'placeholder' => 'Компания' ] ) !!}
                    </div>
                    <div class="margiv-top-10">
                        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
                <!-- END PERSONAL INFO TAB -->

                <!-- BINDS TAB -->
                <div class="tab-pane" id="binds">
                    {!! Form::model( $user, [ 'method' => 'put', 'route' => [ 'users.update', $user->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                    {!! Form::hidden( 'action', 'edit_binds' ) !!}
                    <div class="mt-checkbox-list">
                        @foreach ( $user->managements as $management )
                            <label class="mt-checkbox mt-checkbox-outline">
                                {{ $management->name }}
                                {!! Form::checkbox( 'managements[]', $management->id, true ) !!}
                                <span></span>
                            </label>
                        @endforeach
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            {!! Form::label( 'add_management', 'Добавить УО', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::select( 'managements[]', [ null => ' -- выберите из списка -- ' ] + $managements->toArray(), null, [ 'class' => 'form-control select2', 'data-placeholder' => 'УО', 'id' => 'add_management', 'multiple' ] ) !!}
                        </div>
                    </div>
                    <div class="margiv-top-10">
                        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
                <!-- END BINDS TAB -->

                <!-- PASSWORD TAB -->
                <div class="tab-pane" id="password">
                    {!! Form::model( $user, [ 'method' => 'put', 'route' => [ 'users.update', $user->id ] ] ) !!}
                    {!! Form::hidden( 'action', 'change_password' ) !!}
                    <div class="form-group">
                        {!! Form::label( 'password', 'Пароль', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::password( 'password', [ 'class' => 'form-control', 'placeholder' => 'Пароль' ] ) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label( 'password_confirmation', 'Повторите пароль', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::password( 'password_confirmation', [ 'class' => 'form-control', 'placeholder' => 'Повторите пароль' ] ) !!}
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
                        <span class="caption-subject font-blue-madison bold uppercase">Состояние учетной записи</span>
                    </div>

                    <div class="row margin-top-15 margin-bottom-15">
                        <div class="col-md-12">
                            {!! Form::checkbox( 'active', 1, $user->active, [ 'class' => 'make-switch', 'data-on-color' => 'success', 'data-off-color' => 'danger' ] ) !!}
                        </div>
                    </div>

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

                @if ( \Auth::user()->can( 'admin.logs' ) )
                    <!-- LOGS TAB -->
                    <div class="tab-pane" id="logs">
                        <a href="{{ route( 'logs.index', [ 'author_id' => $user->id ] ) }}" class="btn btn-default" target="_blank">
                            Действия пользователя
                        </a>
                        <a href="{{ route( 'logs.index', [ 'model_name' => \App\User::class, 'model_id' => $user->id ] ) }}" class="btn btn-default" target="_blank">
                            Действия над пользователем
                        </a>
                    </div>
                    <!-- END LOGS TAB -->
                @endif

            </div>
        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/jstree/dist/themes/default/style.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/jstree/dist/jstree.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

                $( '.select2' ).select2();

                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    var target = $(e.target).attr("href");
                    if ( target == '#binds' )
                    {
                        $( '.select2' ).select2();
                    }
                });

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