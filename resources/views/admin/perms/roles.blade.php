@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Права доступа', route( 'perms.index' ) ],
        [ $perm->name, route( 'perms.edit', $perm->id ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.perms.edit' ) )

        <div class="well">
            <a href="{{ route( 'perms.edit', $perm->id ) }}">
                {{ $perm->code }}
                ({{ $perm->name }})
            </a>
        </div>

        {!! Form::model( $perm, [ 'method' => 'put', 'id' => 'perm-edit-form', 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">
            <div class="col-xs-6">
                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
            </div>
            <div class="col-xs-6 text-right">
                <a href="{{ route( 'perms.edit', $perm->id ) }}" class="btn btn-default btn-circle">
                    Редактировать права доступа
                </a>
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-12">
                <div class="mt-checkbox-list">
                    @foreach ( $roles as $role )
                        <label class="mt-checkbox mt-checkbox-outline">
                            {{ $role->name }}
                            {!! Form::checkbox( 'selected_roles[]', $role->id, $perm->roles->contains( 'id', $role->id ) ) !!}
                            <span></span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-6">
                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
            </div>
            <div class="col-xs-6 text-right">
                <a href="{{ route( 'perms.edit', $perm->id ) }}" class="btn btn-default btn-circle">
                    Редактировать права доступа
                </a>
            </div>
        </div>

        {!! Form::close() !!}

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection