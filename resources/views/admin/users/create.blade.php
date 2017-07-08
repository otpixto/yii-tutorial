@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Пользователи', route( 'users.index' ) ],
        [ 'Создать пользователя' ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="portlet light">
        <div class="portlet-body">
            <div class="tab-content">

                <div class="mt-element-step">
                    <div class="row step-line">
                        <div class="col-md-4 mt-step-col first active">
                            <div class="mt-step-number bg-white">1</div>
                            <div class="mt-step-title uppercase font-grey-cascade">Персональные данные</div>
                        </div>
                        <div class="col-md-4 mt-step-col">
                            <div class="mt-step-number bg-white">2</div>
                            <div class="mt-step-title uppercase font-grey-cascade">Логин и пароль</div>
                        </div>
                        <div class="col-md-4 mt-step-col last">
                            <div class="mt-step-number bg-white">3</div>
                            <div class="mt-step-title uppercase font-grey-cascade">Права доступа</div>
                        </div>
                    </div>
                </div>

                {!! Form::open() !!}

                <!-- PERSONAL INFO TAB -->
                <div class="steps" id="step1">
                    <div class="form-group">
                        <label class="control-label">Фамилия</label>
                        {!! Form::text( 'lastname', \Input::old( 'lastname' ), [ 'class' => 'form-control', 'placeholder' => 'Фамилия' ] ) !!}
                    </div>
                    <div class="form-group">
                        <label class="control-label">Имя</label>
                        {!! Form::text( 'firstname', \Input::old( 'firstname' ), [ 'class' => 'form-control', 'placeholder' => 'Имя' ] ) !!}
                    </div>
                    <div class="form-group">
                        <label class="control-label">Отчество</label>
                        {!! Form::text( 'middlename', \Input::old( 'middlename' ), [ 'class' => 'form-control', 'placeholder' => 'Отчество' ] ) !!}
                    </div>
                    <div class="form-group">
                        <label class="control-label">Телефон</label>
                        {!! Form::text( 'phone', \Input::old( 'phone' ), [ 'class' => 'form-control', 'placeholder' => 'Телефон' ] ) !!}
                    </div>
                    <div class="margiv-top-10">
                        {!! Form::button( 'Далее', [ 'class' => 'btn green', 'data-step' => 'next' ] ) !!}
                    </div>
                </div>
                <!-- END PERSONAL INFO TAB -->

                <!-- PASSWORD TAB -->
                <div class="steps hidden" id="step2">
                    <div class="form-group">
                        <label class="control-label">E-mail</label>
                        {!! Form::email( 'email', \Input::old( 'email' ), [ 'class' => 'form-control', 'placeholder' => 'E-mail' ] ) !!}
                    </div>
                    <div class="form-group">
                        <label class="control-label">Пароль</label>
                        {!! Form::password( 'password', [ 'class' => 'form-control', 'placeholder' => 'Пароль' ] ) !!}
                    </div>
                    <div class="form-group">
                        <label class="control-label">Повторите пароль</label>
                        {!! Form::password( 'password_confirm', [ 'class' => 'form-control', 'placeholder' => 'Повторите пароль' ] ) !!}
                    </div>
                    <div class="margiv-top-10">
                        {!! Form::button( 'Далее', [ 'class' => 'btn green', 'data-step' => 'next' ] ) !!}
                        {!! Form::button( 'Назад', [ 'class' => 'btn red', 'data-step' => 'prev' ] ) !!}
                    </div>
                </div>
                <!-- END PASSWORD TAB -->

                <!-- PRIVACY SETTINGS TAB -->
                <div class="steps hidden" id="step3">

                    <div class="caption caption-md">
                        <i class="icon-globe theme-font hide"></i>
                        <span class="caption-subject font-blue-madison bold uppercase">Выберите роли</span>
                    </div>

                    <div class="mt-checkbox-list">
                        @foreach ( $roles as $role )
                            <label class="mt-checkbox mt-checkbox-outline">
                                {{ $role->name }}
                                {!! Form::checkbox( 'roles[]', $role->id ) !!}
                                <span></span>
                            </label>
                        @endforeach
                    </div>

                    <div class="caption caption-md">
                        <i class="icon-globe theme-font hide"></i>
                        <span class="caption-subject font-blue-madison bold uppercase">Выберите права</span>
                    </div>

                    <div class="mt-checkbox-list">
                        @foreach ( $perms as $perm )
                            <label class="mt-checkbox mt-checkbox-outline">
                                {{ $perm->name }}
                                {!! Form::checkbox( 'perms[]', $perm->id ) !!}
                                <span></span>
                            </label>
                        @endforeach
                    </div>

                    <!--end profile-settings-->
                    <div class="margin-top-10">
                        {!! Form::submit( 'Создать', [ 'class' => 'btn green' ] ) !!}
                        {!! Form::button( 'Назад', [ 'class' => 'btn red', 'data-step' => 'prev' ] ) !!}
                    </div>
                </div>
                <!-- END PRIVACY SETTINGS TAB -->

                {!! Form::close() !!}

            </div>
        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )

    <script type="text/javascript">

        $( document )
            .on( 'click', '[data-step]', function ()
            {
                var step = $( this ).attr( 'data-step' );
                var step_nav = $( '.mt-element-step .mt-step-col.active:last' );
                var step_block = $( '.steps:visible:last' );
                switch ( step )
                {
                    case 'next':
                        step_nav.removeClass( 'active' ).addClass( 'done' ).next().addClass( 'active' );
                        step_block.addClass( 'hidden' ).next().removeClass( 'hidden' );
                        break;
                    case 'prev':
                        step_nav.removeClass( 'active' ).prev().removeClass( 'done' ).addClass( 'active' );
                        step_block.addClass( 'hidden' ).prev().removeClass( 'hidden' );
                        break;
                }
            });

    </script>

@endsection