@extends( 'auth.template' )

@section( 'content' )

    <!-- BEGIN LOGIN FORM -->
    {!! Form::open( [ 'class' => 'login-form' ] ) !!}
        <h3 class="form-title font-green">Авторизация</h3>

        @include( 'parts.errors' )

        <div class="alert alert-danger display-hide" role="alert">
            <button class="close" data-close="alert"></button>
            <span>
                Введите логин и пароль
            </span>
        </div>
        <div class="form-group">
            <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
            <label class="control-label visible-ie8 visible-ie9">E-mail</label>
            <input class="form-control form-control-solid placeholder-no-fix" type="text" autocomplete="off" placeholder="E-mail" name="email" />
        </div>
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9">Пароль</label>
            <input class="form-control form-control-solid placeholder-no-fix" type="password" autocomplete="off" placeholder="Пароль" name="password" />
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