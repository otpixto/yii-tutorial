<!-- BEGIN HEADER MENU -->
<div class="nav-collapse collapse navbar-collapse navbar-responsive-collapse">
    <ul class="nav navbar-nav">
        <li class="dropdown more-dropdown @if ( Request::is( '/' ) || Request::is( 'schedule*' ) ) selected @endif">
            <a href="{{ route( 'home' ) }}" class="text-uppercase">
                <i class="fa fa-home"></i>
                Главная
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a href="{{ route( 'home' ) }}">
                        О компании
                    </a>
                </li>
                <li>
                    <a href="{{ route( 'schedule.index' ) }}">
                        График работы операторов
                    </a>
                </li>
            </ul>
        </li>

        @can ( 'tickets.show', 'tickets.create' )
            <li class="dropdown more-dropdown @if ( Request::is( 'tickets*' ) ) selected @endif">
                <a href="{{ route( 'tickets.index' ) }}" class="text-uppercase">
                    <i class="fa fa-support"></i>
                    Обращения
                    @if ( \Session::get( 'tickets_count' ) > 0 )
                        <span class="badge badge-success bold">
                            {{ \Session::get( 'tickets_count' ) }}
                        </span>
                    @endif
                </a>
                <ul class="dropdown-menu">
                    @can ( 'tickets.create' )
                        <li>
                            <a href="{{ route( 'tickets.create' ) }}">
                                Создать обращение
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        @can ( 'works.show', 'works.create' )
            <li class="dropdown more-dropdown @if ( Request::is( 'works*' ) ) selected @endif">
                <a href="{{ route( 'works.index' ) }}" class="text-uppercase">
                    <i class="fa fa-wrench"></i>
                    Работы на сетях
                    @if ( \Session::get( 'works_count' ) > 0 )
                        <span class="badge badge-danger bold">
                            {{ \Session::get( 'works_count' ) }}
                        </span>
                    @endif
                </a>
                <ul class="dropdown-menu">
                    @can ( 'works.create' )
                        <li>
                            <a href="{{ route( 'works.create' ) }}">
                                Создать сообщение
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        @can ( 'catalog.managements.show', 'catalog.categories.show', 'catalog.types.show', 'catalog.categories.show', 'catalog.addresses.show' )
            <li class="dropdown more-dropdown @if ( Request::is( 'catalog*' ) ) selected @endif">
                <a href="javascript:;" class="text-uppercase">
                    <i class="fa fa-edit"></i>
                    Справочники
                </a>
                <ul class="dropdown-menu">
                    @can ( 'catalog.customers.show' )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'customers.index' ) }}" class="nav-link">
                                Заявители
                            </a>
                        </li>
                    @endcan
                    @can ( 'catalog.addresses.show' )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'addresses.index' ) }}" class="nav-link">
                                Здания
                            </a>
                        </li>
                    @endcan
                    @can ( 'catalog.categories.show', 'catalog.types.show' )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'types.index' ) }}" class="nav-link">
                                Классификатор
                            </a>
                        </li>
                    @endcan
                    @can ( 'catalog.managements.show' )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'managements.index' ) }}" class="nav-link">
                                Эксплуатирующие организации (ЭО)
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        @can ( 'admin.users.show', 'admin.perms.show', 'admin.roles.show' )
            <li class="dropdown more-dropdown @if ( Request::is( 'admin*' ) ) selected @endif">
                <a href="javascript:;" class="text-uppercase">
                    <i class="fa fa-lock"></i>
                    Администрирование
                </a>
                <ul class="dropdown-menu">
                    @can ( 'admin.users.show' )
                        <li aria-haspopup="true" class=" ">
                            <a href="/admin/users" class="nav-link">
                                Пользователи
                            </a>
                        </li>
                    @endcan
                    @can ( 'admin.perms.show' )
                        <li aria-haspopup="true" class=" ">
                            <a href="/admin/perms" class="nav-link">
                                Права
                            </a>
                        </li>
                    @endcan
                    @can ( 'admin.roles.show' )
                        <li aria-haspopup="true" class=" ">
                            <a href="/admin/roles" class="nav-link">
                                Роли
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan
    </ul>
</div>
<!-- END HEADER MENU -->