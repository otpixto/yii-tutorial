@extends( 'auth.template' )

@section( 'content' )

    <!-- BEGIN FORGOT PASSWORD FORM -->
    {!! Form::open( [ 'class' => 'forget-form submit-loading' ] ) !!}
    <h3 class="font-green">Забыли пароль?</h3>

    @include( 'parts.errors' )
    @include( 'parts.success' )

    <p>Введите свой e-mail для сброса пароля</p>

    <div class="form-group">
        {!! Form::email( 'email', null, [ 'class' => 'form-control placeholder-no-fix', 'placeholder' => 'E-mail', 'autocomplete' => 'off' ] ) !!}
    </div>
    <div class="form-actions">
        <a href="/login" class="btn green btn-outline">Назад</a>
        {!! Form::submit( 'Сбросить', [ 'class' => 'btn btn-success uppercase pull-right' ] ) !!}
    </div>
    {!! Form::close() !!}
    <!-- END FORGOT PASSWORD FORM -->

@endsection