@extends( 'auth.template' )

@section( 'content' )

    <!-- BEGIN REGISTRATION FORM -->
    {!! Form::open( [ 'class' => 'register-form' ] ) !!}
    <h3 class="font-green">Регистрация</h3>

    @include( 'parts.errors' )

    <p class="hint">Введите свои персональные данные</p>

    <div class="form-group">
        <label class="control-label visible-ie8 visible-ie9">Фамилия</label>
        <input class="form-control placeholder-no-fix" type="text" placeholder="Фамилия" name="lastname" value="{{ \Input::old( 'lastname' ) }}" />
    </div>
    <div class="form-group">
        <label class="control-label visible-ie8 visible-ie9">Имя</label>
        <input class="form-control placeholder-no-fix" type="text" placeholder="Имя" name="firstname" value="{{ \Input::old( 'firstname' ) }}" />
    </div>
    <div class="form-group">
        <label class="control-label visible-ie8 visible-ie9">Отчество</label>
        <input class="form-control placeholder-no-fix" type="text" placeholder="Отчество" name="middlename" value="{{ \Input::old( 'middlename' ) }}" />
    </div>
    <div class="form-group">
        <label class="control-label visible-ie8 visible-ie9">Телефон</label>
        <input class="form-control placeholder-no-fix" type="text" placeholder="Телефон" name="phone" value="{{ \Input::old( 'phone' ) }}" />
    </div>

    <p class="hint">Введите данные для авторизации</p>

    <div class="form-group">
        <label class="control-label visible-ie8 visible-ie9">E-mail</label>
        <input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="E-mail" name="email" value="{{ \Input::old( 'email' ) }}" />
    </div>
    <div class="form-group">
        <label class="control-label visible-ie8 visible-ie9">Пароль</label>
        <input class="form-control placeholder-no-fix" type="password" autocomplete="off" id="register_password" placeholder="Пароль" name="password" />
    </div>
    <div class="form-group">
        <label class="control-label visible-ie8 visible-ie9">Повторите пароль</label>
        <input class="form-control placeholder-no-fix" type="password" autocomplete="off" placeholder="Повторите пароль" name="password_confirmation" />
    </div>
    <div class="form-group margin-top-20 margin-bottom-20">
        <div id="register_tnc_error"> </div>
    </div>
    <div class="form-actions">
        <a href="/login" class="btn green btn-outline">Назад</a>
        <button type="submit" class="btn btn-success uppercase pull-right">Готово</button>
    </div>
    {!! Form::close() !!}
    <!-- END REGISTRATION FORM -->

@endsection