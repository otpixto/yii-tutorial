@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', route( 'home' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'url' => route( 'profile.phone_unreg' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">
            <div class="col-xs-3 control-label">
                Добавочный
            </div>
            <div class="col-xs-6">
                <span class="form-control">
                    {{ \Auth::user()->phoneSession->ext_number }}
                </span>
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-3 control-label">
                Телефон зарегистрирован
            </div>
            <div class="col-xs-6">
                <span class="form-control">
                    {{ \Auth::user()->phoneSession->created_at->format( 'd.m.Y H:i' ) }}
                </span>
            </div>
        </div>

        <div class="form-group">
            <div class=" col-xs-offset-3 col-xs-6">
                <button type="submit" class="btn btn-danger">
                    Разлогинить телефон
                </button>
            </div>
        </div>

    {!! Form::close() !!}

@endsection