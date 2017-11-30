@extends( 'auth.template' )

@section( 'content' )

    <!-- BEGIN LOGIN FORM -->
    {!! Form::open( [ 'class' => 'submit-loading' ] ) !!}
        <h3 class="form-title font-green">Авторизация</h3>

        @include( 'parts.errors' )
        @include( 'parts.success' )

        <div class="form-group">
            <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
            {!! Form::label( 'email', 'E-mail', [ 'class' => 'control-label visible-ie8 visible-ie9' ] ) !!}
            {!! Form::email( 'email', \Input::old( 'email' ), [ 'class' => 'form-control form-control-solid placeholder-no-fix', 'autocomplete' => 'off', 'placeholder' => 'E-mail', 'required' => 'required' ] ) !!}
        </div>

        <div class="form-group">
            {!! Form::label( 'password', 'Пароль', [ 'class' => 'control-label visible-ie8 visible-ie9' ] ) !!}
            {!! Form::password( 'password', [ 'class' => 'form-control form-control-solid placeholder-no-fix', 'autocomplete' => 'off', 'placeholder' => 'Пароль', 'required' => 'required' ] ) !!}
        </div>

        <div class="form-actions">
            <button type="submit" class="btn green uppercase">Войти</button>
            <label class="rememberme check mt-checkbox mt-checkbox-outline">
                <input type="checkbox" name="remember" value="1" />Запомнить
                <span></span>
            </label>
        </div>

        <div class="form-group">
            <p class="text-right">
                <a href="{{ route( 'forgot' ) }}" id="register-btn" class="uppercase">Забыли пароль?</a>
            </p>
        </div>

        <div class="create-account">
            <p>
                <a href="/register" id="register-btn" class="uppercase">Зарегистрироваться</a>
            </p>
        </div>

    {!! Form::close() !!}
    <!-- END LOGIN FORM -->

@endsection