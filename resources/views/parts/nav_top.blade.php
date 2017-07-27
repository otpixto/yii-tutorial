<!-- BEGIN MEGA MENU -->
<!-- DOC: Apply "hor-menu-light" class after the "hor-menu" class below to have a horizontal menu with white background -->
<!-- DOC: Remove data-hover="dropdown" and data-close-others="true" attributes below to disable the dropdown opening on mouse hover -->
<div class="hor-menu  ">
    <ul class="nav navbar-nav">
        @can ( 'tickets.show', 'tickets.create' )
            <li aria-haspopup="true" class="menu-dropdown classic-menu-dropdown">
                <a href="javascript:;">
                    Обращения
                    <span class="arrow"></span>
                </a>
                <ul class="dropdown-menu pull-left">
                    @can ( 'tickets.create' )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'tickets.create' ) }}" class="nav-link">
                                Создать обращение
                            </a>
                        </li>
                    @endcan
                    @can ( 'tickets.show' )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'tickets.index' ) }}" class="nav-link">
                                Реестр заявок
                                <span class="badge badge-success">
                                    {{ \Session::get( 'tickets_count' ) }}
                                </span>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        @can ( 'reports' )
            <li aria-haspopup="true" class="menu-dropdown classic-menu-dropdown">
                <a href="javascript:;">
                    Отчеты
                    <span class="arrow"></span>
                </a>
                <ul class="dropdown-menu pull-left">
                    <li aria-haspopup="true" class=" ">
                        <a href="" class="nav-link">
                            Отчет-1
                        </a>
                    </li>
                    <li aria-haspopup="true" class=" ">
                        <a href="" class="nav-link">
                            Отчет-2
                        </a>
                    </li>
                    <li aria-haspopup="true" class=" ">
                        <a href="" class="nav-link">
                            Отчет-3
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        @can ( 'docs' )
            <li aria-haspopup="true" class="menu-dropdown classic-menu-dropdown">
                <a href="javascript:;">
                    Документация
                    <span class="arrow"></span>
                </a>
                <ul class="dropdown-menu pull-left">
                    <li aria-haspopup="true" class=" ">
                        <a href="" class="nav-link">
                            Документация-1
                        </a>
                    </li>
                    <li aria-haspopup="true" class=" ">
                        <a href="" class="nav-link">
                            Документация-2
                        </a>
                    </li>
                    <li aria-haspopup="true" class=" ">
                        <a href="" class="nav-link">
                            Документация-3
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        @can ( 'catalog.managements.show', 'catalog.categories.show', 'catalog.types.show', 'catalog.categories.show', 'catalog.addresses.show' )
            <li aria-haspopup="true" class="menu-dropdown classic-menu-dropdown">
                <a href="javascript:;">
                    Справочники
                    <span class="arrow"></span>
                </a>
                <ul class="dropdown-menu pull-left">
                    @can ( 'catalog.managements.show' )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'managements.index' ) }}" class="nav-link">
                                Исполнители
                            </a>
                        </li>
                    @endcan
                    @can ( 'catalog.categories.show' )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'categories.index' ) }}" class="nav-link">
                                Категории обращений
                            </a>
                        </li>
                    @endcan
                    @can ( 'catalog.types.show' )
                        <li aria-haspopup="true" class=" ">
                            <a href="{{ route( 'types.index' ) }}" class="nav-link">
                                Типы обращений
                            </a>
                        </li>
                    @endcan
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
                                Адреса
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        @can ( 'admin.users.show', 'admin.perms.show', 'admin.roles.show' )
            <li aria-haspopup="true" class="menu-dropdown classic-menu-dropdown">
                <a href="javascript:;">
                    Администрирование
                    <span class="arrow"></span>
                </a>
                <ul class="dropdown-menu pull-left">
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
<!-- END MEGA MENU -->