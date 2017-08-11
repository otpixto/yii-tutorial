@extends( 'auth.template' )

@section( 'content' )

    @include( 'parts.errors' )
    @include( 'parts.success' )

    <!-- BEGIN RESET PASSWORD FORM -->
    {!! Form::open( [ 'url' => '/reset', 'class' => 'forget-form' ] ) !!}
    <h3 class="font-green">Сброс пароля</h3>

    @include( 'parts.errors' )
    @include( 'parts.success' )

    {!! Form::hidden( 'token', $token ) !!}

    <div class="form-group">
        {!! Form::email( 'email', $email, [ 'class' => 'form-control placeholder-no-fix', 'placeholder' => 'E-mail', 'autocomplete' => 'off', 'readonly' ] ) !!}
    </div>

    <div class="form-group">
        {!! Form::password( 'password', [ 'class' => 'form-control placeholder-no-fix', 'placeholder' => 'Пароль', 'autocomplete' => 'off' ] ) !!}
    </div>

    <div class="form-group">
        {!! Form::password( 'password_confirmation', [ 'class' => 'form-control placeholder-no-fix', 'placeholder' => 'Повторите пароль', 'autocomplete' => 'off' ] ) !!}
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-success uppercase pull-right">Сбросить</button>
    </div>
    {!! Form::close() !!}
    <!-- END RESET PASSWORD FORM -->

@endsection