@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Пользователи', route( 'users.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.users.create' ) )

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

                    {!! Form::open( [ 'url' => route( 'users.store' ) ] ) !!}

                    <!-- PERSONAL INFO TAB -->
                    <div class="steps" id="step1" data-step="1">
                        <div class="form-group">
                            {!! Form::label( 'providers', 'Поставщик', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::select( 'providers[]', $providers->pluck( 'name', 'id' ), \Input::old( 'providers' ), [ 'class' => 'form-control select2', 'data-placeholder' => 'Поставщик', 'multiple' ] ) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label( 'lastname', 'Фамилия', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::text( 'lastname', \Input::old( 'lastname' ), [ 'class' => 'form-control', 'placeholder' => 'Фамилия' ] ) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label( 'firstname', 'Имя', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::text( 'firstname', \Input::old( 'firstname' ), [ 'class' => 'form-control', 'placeholder' => 'Имя' ] ) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label( 'middlename', 'Отчество', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::text( 'middlename', \Input::old( 'middlename' ), [ 'class' => 'form-control', 'placeholder' => 'Отчество' ] ) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::text( 'phone', \Input::old( 'phone' ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
                        </div>
                        <div class="margiv-top-10">
                            {!! Form::button( 'Далее', [ 'class' => 'btn green', 'data-step' => 'next' ] ) !!}
                        </div>
                    </div>
                    <!-- END PERSONAL INFO TAB -->

                    <!-- PASSWORD TAB -->
                    <div class="steps hidden" id="step2" data-step="2">
                        <div class="form-group">
                            {!! Form::label( 'email', 'E-mail', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::email( 'email', \Input::old( 'email' ), [ 'class' => 'form-control', 'placeholder' => 'E-mail' ] ) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label( 'password', 'Пароль', [ 'class' => 'control-label' ] ) !!}
                            <div class="input-group">
                                {!! Form::password( 'password', [ 'class' => 'form-control', 'placeholder' => 'Пароль' ] ) !!}
                                <span class="input-group-btn">
                                    <button id="genpassword" class="btn btn-info" type="button">
                                        <i class="fa fa-arrow-left fa-fw"></i>
                                        случайный
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label( 'password_confirmation', 'Повторите пароль', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::password( 'password_confirmation', [ 'class' => 'form-control', 'placeholder' => 'Повторите пароль' ] ) !!}
                        </div>
                        <div class="margiv-top-10">
                            {!! Form::button( 'Далее', [ 'class' => 'btn green', 'data-step' => 'next' ] ) !!}
                            {!! Form::button( 'Назад', [ 'class' => 'btn red', 'data-step' => 'prev' ] ) !!}
                        </div>
                    </div>
                    <!-- END PASSWORD TAB -->

                    <!-- PRIVACY SETTINGS TAB -->
                    <div class="steps hidden" id="step3" data-step="3">

                        <div class="caption caption-md">
                            <i class="icon-globe theme-font hide"></i>
                            <span class="caption-subject font-blue-madison bold uppercase">Выберите роли</span>
                        </div>

                        <div class="mt-checkbox-list">
                            @foreach ( $roles as $_role )
                                <label class="mt-checkbox mt-checkbox-outline">
                                    {{ $_role->name }}
                                    {!! Form::checkbox( 'roles[]', $_role->code ) !!}
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

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/jstree/dist/themes/default/style.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )

    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

            })

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
            })

            .on( 'click', '#genpassword', function ( e )
            {

                e.preventDefault();

                var password = genPassword( 6 );
                alert( 'Сохраните этот пароль: ' + password );
                $( '#password, #password_confirmation' ).val( password );

            });

    </script>

@endsection