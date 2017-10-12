@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', route( 'home' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'class' => 'form-horizontal submit-loading' ] ) !!}
    <div class="form-group">
        {!! Form::label( 'number', 'Номер', [ 'class' => 'control-label col-xs-3' ] ) !!}
        <div class="col-xs-6">
            {!! Form::text( 'number', $number, [ 'class' => 'form-control', 'maxlength' => 10, 'readonly' ] ) !!}
        </div>
    </div>
    <div class="form-group">
        {!! Form::label( 'code', 'Код', [ 'class' => 'control-label col-xs-3' ] ) !!}
        <div class="col-xs-6">
            {!! Form::text( 'code', null, [ 'class' => 'form-control', 'maxlength' => 4, 'autofocus' ] ) !!}
        </div>
    </div>
    <div class="form-group">
        <div class="col-xs-offset-3 col-xs-6">
            <div class="progress progress-striped active" style="margin-bottom:0;">
                <div class="progress-bar progress-bar-warning" style="width: 100%">
                    Осталось:
                    <b data-left="{{ \Carbon\Carbon::now()->addSeconds( \App\Models\UserPhoneAuth::$timeout )->toDateTimeString() }}"></b>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class=" col-xs-offset-3 col-xs-6">
            {!! Form::submit( 'Подтвердить', [ 'class' => 'btn btn-primary' ] ) !!}
        </div>
    </div>
    {!! Form::close() !!}

@endsection

@section( 'js' )

    <script type="text/javascript">

        function downtimers ()
        {
            var that = $( '[data-left]' );
            var d, h, m;
            var t = that.data('left').split(/[^0-9]/);
            var s = Math.floor(((new Date (t[0], t[1]-1, t[2], t[3]?t[3]:0, t[4]?t[4]:0, t[5]?t[5]:0)).getTime() - (new Date().getTime())) / 1000);
            if ( s < 0 )
            {
                window.location.href = "{{ route( 'profile.phone_reg' ) }}";
            }
            else
            {
                s-=(d=Math.floor(s/60/60/24))*24*60*60;
                s-=(h=Math.floor(s/60/60))*60*60;
                s-=(m=Math.floor(s/60))*60;
                that.text(
                    (s<10?'0'+s:s).plural(" секунд "," секунда "," секунды ")
                );
            }
        }

        downtimers();

        setInterval( downtimers, 1000 );

    </script>

@endsection