@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Пользователи', route( 'users.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'admin.users.edit' ) )

        <div class="row">
            <div class="col-md-3">
                <a href="{{ route( 'users.edit', $user->id ) . '?' . rand( 111, 999 ) }}#photo" class="btn btn-xs bg-blue-hoki bg-font-blue-hoki pull-right">
                    <i class="fa fa-upload tooltips" data-placement="left" title="Загрузить изображение"></i>
                </a>
                <div class="portlet light bordered">
                    <img src="/images/nophoto.png" class="img-responsive" alt="" />
                </div>
                <div class="list-group">
                    <a href="{{ route( 'users.edit', $user->id ) }}" class="list-group-item @if ( \Request::route()->getName() == 'users.edit' ) active @endif">
                        <i class="fa fa-user"></i>
                        Редактировать
                    </a>
                    <a href="{{ route( 'users.perms', $user->id ) }}" class="list-group-item @if ( \Request::route()->getName() == 'users.perms' ) active @endif">
                        <i class="fa fa-gear"></i>
                        Права доступа
                    </a>
                    <a href="{{ route( 'users.providers', $user->id ) }}" class="list-group-item @if ( \Request::route()->getName() == 'users.providers' ) active @endif">
                        <i class="fa fa-link"></i>
                        Поставщики
                        <span class="badge">
                            {{ $user->providers()->count() }}
                        </span>
                    </a>
                    <a href="{{ route( 'users.managements', $user->id ) }}" class="list-group-item @if ( \Request::route()->getName() == 'users.managements' ) active @endif">
                        <i class="fa fa-link"></i>
                        Привязка УО
                        <span class="badge">
                            {{ $user->managements()->count() }}
                        </span>
                    </a>
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.logs' ) )
                        <a href="{{ route( 'users.logs', $user->id ) }}" class="list-group-item @if ( \Request::route()->getName() == 'users.logs' ) active @endif">
                            <i class="fa fa-info"></i>
                            Логи пользователя
                        </a>
                    @endif
                </div>
                @if ( ( \Auth::user()->admin || \Auth::user()->can( 'admin.loginas' ) ) && \Auth::user()->id != $user->id && ( $user->can( 'supervisor.all_providers' ) || $user->providers()->count() ) )
                    <a href="{{ route( 'loginas', $user->id ) }}" class="btn btn-warning btn-block btn-lg margin-top-10">
                        <i class="fa fa-sign-in"></i>
                        Войти под этим пользователем
                    </a>
                @endif
            </div>
            <div class="col-md-9">
                <div class="portlet light profile-sidebar-portlet bordered">
                    <h2>
                        {!! $user->getName() !!}
                        @if ( ! empty( $user->prefix ) )
                            <span class="small text-muted">
                                {{ $user->prefix }}
                            </span>
                        @endif
                        <span class="label label-{{ $user->active ? 'success' : 'danger' }} pull-right">
                            ID: <strong id="user-id">{{ $user->id }}</strong>
                        </span>
                    </h2>
                    <h6>
                        {{ $user->roles->implode( 'name', ', ' ) }}
                    </h6>
                    <hr />
                    @yield( 'users.content' )
                </div>
            </div>
        </div>

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'css' )
    <style>
        .list-group .fa {
            width: 16px;
            height: 16px;
            margin-right: 4px;
        }
    </style>
    @yield( 'users.css' )
@endsection