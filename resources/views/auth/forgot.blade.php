@extends( 'auth.template' )

@section( 'content' )

    <!-- BEGIN FORGOT PASSWORD FORM -->
    {!! Form::open( [ 'class' => 'forget-form' ] ) !!}
    <h3 class="font-green">Забыли пароль?</h3>

    @include( 'parts.errors' )

    <p>Введите свой e-mail для сброса пароля</p>

    <div class="form-group">
        <input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="E-mail" name="email" /> </div>
    <div class="form-actions">
        <a href="/login" class="btn green btn-outline">Назад</a>
        <button type="submit" class="btn btn-success uppercase pull-right">Сбросить</button>
    </div>
    {!! Form::close() !!}
    <!-- END FORGOT PASSWORD FORM -->

@endsection