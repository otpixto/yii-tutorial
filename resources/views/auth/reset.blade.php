@extends( 'auth.template' )

@section( 'content' )

    @include( 'parts.errors' )
    @include( 'parts.success' )

    <!-- BEGIN RESET PASSWORD FORM -->
    {!! Form::open( [ 'url' => '/reset', 'class' => 'forget-form submit-loading' ] ) !!}
    <h3 class="font-green">Сброс пароля</h3>

    @include( 'parts.errors' )
    @include( 'parts.success' )

    {!! Form::hidden( 'token', $token ) !!}

    <div class="form-group">
        {!! Form::label( 'email', 'E-mail', [ 'class' => 'control-label visible-ie8 visible-ie9' ] ) !!}
        {!! Form::email( 'email', $email, [ 'class' => 'form-control placeholder-no-fix', 'placeholder' => 'E-mail', 'autocomplete' => 'off', 'readonly' ] ) !!}
    </div>

    <div class="form-group">
        {!! Form::label( 'password', 'Пароль', [ 'class' => 'control-label visible-ie8 visible-ie9' ] ) !!}
        <div class="input-group">
            {!! Form::password( 'password', [ 'class' => 'form-control placeholder-no-fix', 'placeholder' => 'Пароль', 'autocomplete' => 'off', 'required' ] ) !!}
            <span class="input-group-btn">
                <button id="genpassword" class="btn btn-info" type="button">
                    <i class="fa fa-arrow-left fa-fw"></i>
                    случайный
                </button>
            </span>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label( 'password_confirmation', 'Повторите пароль', [ 'class' => 'control-label visible-ie8 visible-ie9' ] ) !!}
        {!! Form::password( 'password_confirmation', [ 'class' => 'form-control placeholder-no-fix', 'placeholder' => 'Повторите пароль', 'autocomplete' => 'off', 'required' ] ) !!}
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-success uppercase pull-right">Сбросить</button>
    </div>
    {!! Form::close() !!}
    <!-- END RESET PASSWORD FORM -->

@endsection

@section( 'js' )
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .on( 'click', '#genpassword', function ( e )
            {

                e.preventDefault();

                var password = genPassword( 6 );
                alert( 'Сохраните этот пароль: ' + password );
                $( '#password, #password_confirmation' ).val( password );

            });

    </script>
@endsection