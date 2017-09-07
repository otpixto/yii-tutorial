<!-- BEGIN HEADER MENU -->
<div class="nav-collapse collapse navbar-collapse navbar-responsive-collapse">
    <ul class="nav navbar-nav">

        <li class="dropdown more-dropdown @if ( Request::is( 'about' ) || Request::is( 'schedule*' ) ) selected @endif">
            <a href="{{ route( 'about' ) }}" class="text-uppercase">
                <i class="fa fa-home"></i>
                Главная
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a href="{{ route( 'about' ) }}">
                        О компании
                    </a>
                </li>
                @if ( \Auth::user()->admin || \Auth::user()->can( 'schedule' ) )
                    <li>
                        <a href="{{ route( 'schedule.index' ) }}">
                            График работы операторов
                        </a>
                    </li>
                @endif
            </ul>
        </li>

        @if ( \Auth::user()->admin || \Auth::user()->can( 'tickets.show', 'tickets.create', 'tickets.call', 'tickets.closed', 'tickets.no_contract' ) )
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
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'tickets.create' ) )
                        <li>
                            <a href="{{ route( 'tickets.create' ) }}">
                                Создать обращение
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'tickets.call' ) )
                        <li>
                            <a href="{{ route( 'tickets.call' ) }}">
                                Обзвон
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'tickets.closed' ) )
                        <li>
                            <a href="{{ route( 'tickets.closed' ) }}">
                                Закрытые обращения
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'tickets.no_contract' ) )
                        <li>
                            <a href="{{ route( 'tickets.no_contract' ) }}">
                                Отсутствует договор
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'tickets.canceled' ) )
                        <li>
                            <a href="{{ route( 'tickets.canceled' ) }}">
                                Отмененные обращения
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if ( \Auth::user()->admin || \Auth::user()->can( 'works.show', 'works.create', 'works.all' ) )
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
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'works.create' ) )
                        <li>
                            <a href="{{ route( 'works.create' ) }}">
                                Создать сообщение
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'works.all' ) )
                        <li>
                            <a href="{{ route( 'works.index' ) }}?show=all">
                                Работы за все время
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if ( \Auth::user()->admin || \Auth::user()->can( 'catalog.managements.show', 'catalog.categories.show', 'catalog.types.show', 'catalog.categories.show', 'catalog.addresses.show' ) )
            <li class="dropdown more-dropdown @if ( Request::is( 'catalog*' ) ) selected @endif">
                <a href="javascript:;" class="text-uppercase">
                    <i class="fa fa-edit"></i>
                    Справочники
                </a>
                <ul class="dropdown-menu">
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'catalog.customers.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'customers.index' ) }}" class="nav-link">
                                Заявители
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'catalog.addresses.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'addresses.index' ) }}" class="nav-link">
                                Здания
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'catalog.types.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'types.index' ) }}" class="nav-link">
                                Классификатор
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'catalog.managements.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'managements.index' ) }}" class="nav-link">
                                Эксплуатирующие организации (ЭО)
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.users.show', 'admin.perms.show', 'admin.roles.show' ) )
            <li class="dropdown more-dropdown @if ( Request::is( 'admin*' ) ) selected @endif">
                <a href="javascript:;" class="text-uppercase">
                    <i class="fa fa-lock"></i>
                    Администрирование
                </a>
                <ul class="dropdown-menu">
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.users.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="/admin/users" class="nav-link">
                                Пользователи
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.perms.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="/admin/perms" class="nav-link">
                                Права
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.roles.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="/admin/roles" class="nav-link">
                                Роли
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

    </ul>
</div>
<!-- END HEADER MENU -->