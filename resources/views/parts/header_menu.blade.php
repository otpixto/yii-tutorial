<!-- BEGIN HEADER MENU -->
<div class="nav-collapse collapse navbar-collapse navbar-responsive-collapse">
    <ul class="nav navbar-nav pull-right">

        @if ( \Auth::user()->canOne( 'tickets.show', 'tickets.create', 'tickets.call' ) )
            <li class="dropdown more-dropdown @if ( Request::is( 'tickets*' ) ) selected @endif">
                <a href="{{ route( 'tickets.index' ) }}" class="text-uppercase">
                    <i class="fa fa-support"></i>
                    Заявки
                    @if ( \Auth::user()->can( 'tickets.counter' ) )
                        <span class="badge badge-info bold">
                            {{ \App\Classes\Counter::ticketsCount() }}
                        </span>
                        <span class="badge badge-danger bold">
                            {{ \App\Classes\Counter::ticketsOverdueCount() }}
                        </span>
                        @if ( \Auth::user()->can( 'tickets.call' ) )
                            <span class="badge badge-warning bold">
                                {{ \App\Classes\Counter::ticketsCountByStatus( 'confirmation_client' ) }}
                            </span>
                        @endif
                    @endif
                </a>
                <ul class="dropdown-menu">
                    @if ( \Auth::user()->can( 'tickets.create' ) )
                        <li>
                            <a href="{{ route( 'tickets.create' ) }}">
                                Создать заявку
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'tickets.show' ) )
                        <li>
                            <a href="{{ route( 'tickets.index' ) }}?show=overdue">
                                Просроченные
                                @if ( \Auth::user()->can( 'tickets.counter' ) )
                                    <span class="badge badge-danger bold">
                                        {{ \App\Classes\Counter::ticketsOverdueCount() }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'tickets.call' ) )
                        <li>
                            <a href="{{ route( 'tickets.index', [ 'statuses' => 'confirmation_client', 'show' => 'mine' ] ) }}">
                                Обзвон
                                @if ( \Auth::user()->can( 'tickets.counter' ) )
                                    <span class="badge badge-warning bold">
                                        {{ \App\Classes\Counter::ticketsCountByStatus( 'confirmation_client' ) }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'tickets.calendar' ) )
                        <li>
                            <a href="{{ route( 'tickets.calendar', \Carbon\Carbon::now()->format( 'm.Y' ) ) }}">
                                Календарь
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if ( \Auth::user()->canOne( 'works.show', 'works.create', 'works.all' ) )
            <li class="dropdown more-dropdown @if ( Request::is( 'works*' ) ) selected @endif">
                <a href="{{ route( 'works.index' ) }}" class="text-uppercase">
                    <i class="fa fa-wrench"></i>
                    Отключения
                    @if ( ( \Auth::user()->can( 'works.counter' ) ) )
                        <span class="badge badge-info bold">
                            {{ \App\Classes\Counter::worksCount() }}
                        </span>
                        <span class="badge badge-danger bold">
                            {{ \App\Classes\Counter::worksOverdueCount() }}
                        </span>
                    @endif
                </a>
                <ul class="dropdown-menu">
                    @if ( \Auth::user()->can( 'works.create' ) )
                        <li>
                            <a href="{{ route( 'works.create' ) }}">
                                Создать сообщение
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="{{ route( 'works.index' ) }}?show=overdue">
                            Просроченные
                            @if ( \Auth::user()->can( 'works.counter' ) )
                                <span class="badge badge-danger bold">
                                    {{ \App\Classes\Counter::worksOverdueCount() }}
                                </span>
                            @endif
                        </a>
                    </li>
                    @if ( \Auth::user()->can( 'works.all' ) )
                        <li>
                            <a href="{{ route( 'works.index' ) }}?show=all">
                                Отключения за все время
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if ( \Auth::user()->canOne( 'reports.managements', 'reports.types', 'reports.rates', 'reports.map' ) )
            <li class="dropdown more-dropdown @if ( Request::is( 'reports*' ) ) selected @endif">
                <a {{--href="{{ route( 'reports.index' ) }}"--}} class="text-uppercase">
                    <i class="fa fa-bar-chart"></i>
                    <span class="hidden-md">
                        Отчеты
                    </span>
                </a>
                <ul class="dropdown-menu">
                    @if ( \Auth::user()->can( 'reports.tickets' ) )
                        <li>
                            <a href="{{ route( 'reports.tickets' ) }}">
                                Статистика по заявкам
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'reports.types' ) )
                        <li>
                            <a href="{{ route( 'reports.types' ) }}">
                                Статистика по категориям
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'reports.rates' ) )
                        <li>
                            <a href="{{ route( 'reports.rates' ) }}">
                                Статистика оценок
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'reports.operators' ) )
                        <li>
                            <a href="{{ route( 'reports.operators' ) }}">
                                Статистика по операторам
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'reports.executors' ) )
                        <li>
                            <a href="{{ route( 'reports.executors' ) }}">
                                Отчет по исполнителю
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'reports.addresses' ) )
                        <li>
                            <a href="{{ route( 'reports.addresses' ) }}">
                                Отчет по адресу
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if ( \Auth::user()->canOne( 'maps.zones.show', 'maps.zones.edit', 'maps.tickets', 'maps.works' ) )
            <li class="dropdown more-dropdown @if ( Request::is( 'maps*' ) ) selected @endif">
                <a href="javascript:;" class="text-uppercase">
                    <i class="fa fa-map"></i>
                    <span class="hidden-md">
                        Карты
                    </span>
                </a>
                <ul class="dropdown-menu">
                @if ( \Auth::user()->can( 'maps.tickets' ) )
                    <li>
                        <a href="{{ route( 'maps.tickets' ) }}">
                            География обращений
                        </a>
                    </li>
                @endif
                @if ( \Auth::user()->can( 'maps.works' ) )
                    <li>
                        <a href="{{ route( 'maps.works' ) }}">
                            География отключений
                        </a>
                    </li>
                @endif
                @if ( \Auth::user()->canOne( 'maps.zones.show', 'maps.zones.edit' ) )
                    <li>
                        <a href="{{ route( 'zones.index' ) }}">
                            Зоны обслуживания
                        </a>
                    </li>
                @endif
                </ul>
            </li>
        @endif

        @if ( \Auth::user()->canOne( 'catalog.managements.show', 'catalog.types.show', 'catalog.categories.show', 'catalog.buildings.show', 'catalog.segments.show', 'catalog.groups.show' ) )
            <li class="dropdown more-dropdown @if ( Request::is( 'catalog*' ) ) selected @endif">
                <a href="javascript:;" class="text-uppercase">
                    <i class="fa fa-edit"></i>
                    <span class="hidden-md">
                        Справочники
                    </span>
                </a>
                <ul class="dropdown-menu">
                    @if ( \Auth::user()->can( 'catalog.customers.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'customers.index' ) }}" class="nav-link">
                                Заявители
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'catalog.segments.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'segments.index' ) }}" class="nav-link">
                                Сегменты
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'catalog.buildings.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'buildings.index' ) }}" class="nav-link">
                                Здания
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'catalog.groups.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'groups.index' ) }}" class="nav-link">
                                Группы
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'catalog.types.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'types.index' ) }}" class="nav-link">
                                Классификатор
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'catalog.managements.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'managements.index' ) }}" class="nav-link">
                                Управляющие организации (УО)
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'catalog.executors.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'executors.index' ) }}" class="nav-link">
                                Исполнители
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if ( \Auth::user()->admin || \Auth::user()->canOne( 'admin.users.show', 'admin.sessions.show', 'admin.calls.show', 'admin.logs.show' ) )
            <li class="dropdown more-dropdown @if ( Request::is( 'admin*' ) ) selected @endif">
                <a href="javascript:;" class="text-uppercase">
                    <i class="fa fa-lock"></i>
                    <span class="hidden-md">
                        Админ
                    </span>
                </a>
                <ul class="dropdown-menu">
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.users.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'users.index' ) }}" class="nav-link">
                                Пользователи
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.sessions.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'sessions.index' ) }}" class="nav-link">
                                Телефонные сессии
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.calls.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'calls.index' ) }}" class="nav-link">
                                Телефонные звонки
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.logs' ) )
                         <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'logs.index' ) }}" class="nav-link">
                                Системные логи
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->admin )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'perms.index' ) }}" class="nav-link">
                                <i class="fa fa-star text-warning"></i>
                                Права
                            </a>
                        </li>
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'roles.index' ) }}" class="nav-link">
                                <i class="fa fa-star text-warning"></i>
                                Роли
                            </a>
                        </li>
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'providers.index' ) }}" class="nav-link">
                                <i class="fa fa-star text-warning"></i>
                                Поставщики
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        <li class="dropdown more-dropdown @if ( Request::is( 'catalog*' ) ) selected @endif">
            <a href="javascript:;" class="text-uppercase">
                <i class="fa fa-user"></i>
                <span class="hidden-md">
                    {{ \Auth::user()->getShortName( true ) }}
                </span>
            </a>
            <ul class="dropdown-menu">
                <li aria-haspopup="true" class="">
                    <a href="{{ route( 'profile.phone' ) }}">
                        Телефон
                    </a>
                </li>
                <li aria-haspopup="true" class="">
                    <a href="{{ route( 'logout' ) }}" class="nav-link">
                        Выход
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</div>
<!-- END HEADER MENU -->