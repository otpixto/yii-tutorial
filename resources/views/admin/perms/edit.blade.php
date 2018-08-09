@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Права доступа', route( 'perms.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin )

        {!! Form::model( $perm, [ 'method' => 'put', 'route' => [ 'perms.update', $perm->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">
            <div class="col-xs-12">
                {!! Form::label( 'code', 'Код', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'code', \Input::old( 'code', $perm->code ), [ 'class' => 'form-control', 'placeholder' => 'Код' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-12">
                {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'name', \Input::old( 'name', $perm->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-12">
                {!! Form::label( 'guard', 'Guard', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'guard', $guards, \Input::old( 'guard', $perm->guard ), [ 'class' => 'form-control' ] ) !!}
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-6">
                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
            </div>
            <div class="col-xs-6 text-right">
                <a href="{{ route( 'perms.roles', $perm->id ) }}" class="btn btn-default btn-circle">
                    Привязка к ролям
                    <span class="badge">
                        {{ $perm->roles()->count() }}
                    </span>
                </a>
                <a href="{{ route( 'perms.users', $perm->id ) }}" class="btn btn-default btn-circle">
                    Привязка к пользователям
                    <span class="badge">
                        {{ $perm->users()->count() }}
                    </span>
                </a>
            </div>
        </div>

        {!! Form::close() !!}

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection