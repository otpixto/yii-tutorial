<!-- BEGIN HEADER MENU -->
<div class="nav-collapse collapse navbar-collapse navbar-responsive-collapse">
    <ul class="nav navbar-nav">

        @if ( \Auth::user()->canOne( 'tickets.show', 'tickets.create', 'tickets.call' ) )
            <li class="dropdown more-dropdown @if ( Request::is( 'tickets*' ) ) selected @endif">
                <a href="{{ route( 'tickets.index' ) }}" class="text-uppercase">
                    <i class="fa fa-support"></i>
                    <span class="hidden-md">
                        Заявки
                    </span>
                    @if ( \Auth::user()->can( 'tickets.counter' ) )
                        <span class="badge badge-info bold">
                            {{ \Cache::tags( 'tickets_counts' )->get( 'user.' . \Auth::user()->id . '.tickets_count' ) }}
                        </span>
                        <span class="badge badge-danger bold">
                            {{ \Cache::tags( 'tickets_counts' )->get( 'user.' . \Auth::user()->id . '.tickets_overdue_count' ) }}
                        </span>
                        @if ( \Auth::user()->can( 'tickets.call' ) )
                            <span class="badge badge-warning bold">
                                {{ \Cache::tags( 'tickets_counts' )->get( 'user.' . \Auth::user()->id . '.tickets_completed_count' ) }}
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
                                        {{ \Cache::tags( 'tickets_counts' )->get( 'user.' . \Auth::user()->id . '.tickets_overdue_count' ) }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'tickets.call' ) )
                        <li>
                            <a href="{{ route( 'tickets.index' ) }}?show=call">
                                Обзвон
                                @if ( \Auth::user()->can( 'tickets.counter' ) )
                                    <span class="badge badge-warning bold">
                                        {{ \Cache::tags( 'tickets_counts' )->get( 'user.' . \Auth::user()->id . '.tickets_completed_count' ) }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'tickets.calendar' ) )
                        <li>
                            <a href="{{ route( 'tickets.calendar', \Carbon\Carbon::now()->format( 'Y-m-d' ) ) }}">
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
                    <span class="hidden-md">
                        Отключения
                    </span>
                    @if ( ( \Auth::user()->can( 'works.counter' ) ) )
                        <span class="badge badge-info bold">
                            {{ \Cache::tags( 'works_counts' )->get( 'user.' . \Auth::user()->id . '.works_count' ) }}
                        </span>
                        <span class="badge badge-danger bold">
                            {{ \Cache::tags( 'works_counts' )->get( 'user.' . \Auth::user()->id . '.works_overdue_count' ) }}
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
                                    {{ \Cache::tags( 'works_counts' )->get( 'user.' . \Auth::user()->id . '.works_overdue_count' ) }}
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
                                Отчет по заявкам
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'reports.types' ) )
                        <li>
                            <a href="{{ route( 'reports.types' ) }}">
                                Отчет по категориям
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'reports.rates' ) )
                        <li>
                            <a href="{{ route( 'reports.rates' ) }}">
                                Отчет по оценкам
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'reports.executors' ) )
                        <li>
                            <a href="{{ route( 'reports.executors' ) }}">
                                Отчет по исполнителям
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'reports.addresses' ) )
                        <li>
                            <a href="{{ route( 'reports.addresses' ) }}">
                                Отчет по адресам
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'reports.operators' ) )
                        <li>
                            <a href="{{ route( 'reports.operators' ) }}">
                                Отчет по операторам
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
                            География работ на сетях
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

        @if ( \Auth::user()->canOne( 'catalog.managements.show', 'catalog.types.show', 'catalog.categories.show', 'catalog.buildings.show', 'catalog.segments.show' ) )
            <li class="dropdown more-dropdown @if ( Request::is( 'catalog*' ) ) selected @endif">
                <a href="javascript:;" class="text-uppercase">
                    <i class="fa fa-edit"></i>
                    <span class="hidden-md">
                        Справочники
                    </span>
                </a>
                <ul class="dropdown-menu">
                    @if ( \Auth::user()->can( 'catalog.users.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'users.index' ) }}" class="nav-link">
                                Пользователи
                            </a>
                        </li>
                    @endif
                    @if ( \Auth::user()->can( 'catalog.customers.show' ) )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'customers.index' ) }}" class="nav-link">
                                Заявители
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
                </ul>
            </li>
        @endif

        @if ( \Auth::user()->admin )
            <li class="dropdown more-dropdown @if ( Request::is( 'admin*' ) ) selected @endif">
                <a href="javascript:;" class="text-uppercase">
                    <i class="fa fa-lock"></i>
                    <span class="hidden-md">
                        Админ
                    </span>
                </a>
                <ul class="dropdown-menu">
                    <li aria-haspopup="true" class=" ">
                        <a href="{{ route( 'perms.index' ) }}" class="nav-link">
                            Права
                        </a>
                    </li>
                    <li aria-haspopup="true" class=" ">
                        <a href="{{ route( 'roles.index' ) }}" class="nav-link">
                            Роли
                        </a>
                    </li>
                    <li aria-haspopup="true" class=" ">
                        <a href="{{ route( 'providers.index' ) }}" class="nav-link">
                            Поставщики
                        </a>
                    </li>
                    <li aria-haspopup="true" class=" ">
                        <a href="{{ route( 'sessions.index' ) }}" class="nav-link">
                            Телефонные сессии
                        </a>
                    </li>
                    <li aria-haspopup="true" class=" ">
                        <a href="{{ route( 'calls.index' ) }}" class="nav-link">
                            Телефонные звонки
                        </a>
                    </li>
                     <li aria-haspopup="true" class=" ">
                        <a href="{{ route( 'logs.index' ) }}" class="nav-link">
                            Системные логи
                        </a>
                    </li>
                </ul>
            </li>
        @endif

    </ul>
</div>
<!-- END HEADER MENU -->