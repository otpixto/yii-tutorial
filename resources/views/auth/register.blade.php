@extends( 'auth.template' )

@section( 'content' )

    @include( 'parts.errors' )
    @include( 'parts.success' )

    <!-- BEGIN REGISTRATION FORM -->
    {!! Form::open( [ 'class' => 'register-form' ] ) !!}
    <h3 class="font-green">Регистрация</h3>

    @include( 'parts.errors' )

    <p class="hint">Введите свои персональные данные</p>

    <div class="form-group">
        {!! Form::label( 'lastname', 'Фамилия', [ 'class' => 'control-label visible-ie8 visible-ie9' ] ) !!}
        {!! Form::text( 'lastname', \Input::old( 'lastname' ), [ 'class' => 'form-control placeholder-no-fix', 'placeholder' => 'Фамилия', 'required' ] ) !!}
    </div>
    <div class="form-group">
        {!! Form::label( 'firstname', 'Имя', [ 'class' => 'control-label visible-ie8 visible-ie9' ] ) !!}
        {!! Form::text( 'firstname', \Input::old( 'firstname' ), [ 'class' => 'form-control placeholder-no-fix', 'placeholder' => 'Имя', 'required' ] ) !!}
    </div>
    <div class="form-group">
        {!! Form::label( 'middlename', 'Отчество', [ 'class' => 'control-label visible-ie8 visible-ie9' ] ) !!}
        {!! Form::text( 'middlename', \Input::old( 'middlename' ), [ 'class' => 'form-control placeholder-no-fix', 'placeholder' => 'Отчество', 'required' ] ) !!}
    </div>
    <div class="form-group">
        {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label visible-ie8 visible-ie9' ] ) !!}
        {!! Form::text( 'phone', \Input::old( 'phone' ), [ 'class' => 'form-control placeholder-no-fix mask_phone', 'placeholder' => 'Телефон', 'required' ] ) !!}
    </div>

    <p class="hint">Введите данные для авторизации</p>

    <div class="form-group">
        {!! Form::label( 'email', 'E-mail', [ 'class' => 'control-label visible-ie8 visible-ie9' ] ) !!}
        {!! Form::email( 'email', \Input::old( 'email' ), [ 'class' => 'form-control placeholder-no-fix', 'placeholder' => 'E-mail', 'required' ] ) !!}
    </div>
    <div class="form-group">
        {!! Form::label( 'password', 'Пароль', [ 'class' => 'control-label visible-ie8 visible-ie9' ] ) !!}
        {!! Form::password( 'password', [ 'class' => 'form-control placeholder-no-fix', 'placeholder' => 'Пароль', 'autocomplete' => 'off', 'required' ] ) !!}
    </div>
    <div class="form-group">
        {!! Form::label( 'password_confirmation', 'Повторите пароль', [ 'class' => 'control-label visible-ie8 visible-ie9' ] ) !!}
        {!! Form::password( 'password_confirmation', [ 'class' => 'form-control placeholder-no-fix', 'placeholder' => 'Повторите пароль', 'autocomplete' => 'off', 'required' ] ) !!}
    </div>
    <div class="form-actions">
        <a href="/login" class="btn green btn-outline">Назад</a>
        <button type="submit" class="btn btn-success uppercase pull-right">Готово</button>
    </div>
    {!! Form::close() !!}
    <!-- END REGISTRATION FORM -->

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
            });

    </script>
@endsection