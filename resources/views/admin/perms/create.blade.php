@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Права', route( 'perms.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.perms.create' ) )

        {!! Form::open( [ 'url' => route( 'perms.store' ) ] ) !!}

        <div class="form-group">
            {!! Form::label( 'code', 'Код', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'code', \Input::old( 'code' ), [ 'class' => 'form-control', 'placeholder' => 'Код' ] ) !!}
        </div>

        <div class="form-group">
            {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
        </div>

        <div class="form-group">
            {!! Form::label( 'guard', 'Guard', [ 'class' => 'control-label' ] ) !!}
            {!! Form::select( 'guard', $guards, \Input::old( 'guard', config( 'defaults.guard' ) ), [ 'class' => 'form-control' ] ) !!}
        </div>

        <div class="caption caption-md">
            <i class="icon-globe theme-font hide"></i>
            <span class="caption-subject font-blue-madison bold uppercase">Применить для ролей</span>
        </div>

        <div class="mt-checkbox-list">
            @foreach ( $roles as $role )
                <label class="mt-checkbox mt-checkbox-outline">
                    {{ $role->name }}
                    {!! Form::checkbox( 'roles[]', $role->code ) !!}
                    <span></span>
                </label>
            @endforeach
        </div>

        <div class="margin-top-10">
            {!! Form::submit( 'Создать', [ 'class' => 'btn green' ] ) !!}
        </div>

        {!! Form::close() !!}

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection