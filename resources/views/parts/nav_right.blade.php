<!-- BEGIN QUICK NAV -->
<nav class="quick-nav">
    <a class="quick-nav-trigger" href="#">
        <span aria-hidden="true"></span>
    </a>
    <ul>
        <li>
            <a href="{{ route( 'tickets.create' ) }}" class="active">
                <span>Создать обращение</span>
                <i class="icon-plus"></i>
            </a>
        </li>
        <li>
            <a href="{{ route( 'tickets.index' ) }}">
                <span>
                    Реестр заявок
                    ({{ \Session::get( 'tickets_count' ) }})
                </span>
                <i class="icon-list"></i>
            </a>
        </li>
    </ul>
    <span aria-hidden="true" class="quick-nav-bg"></span>
</nav>
<div class="quick-nav-overlay"></div>
<!-- END QUICK NAV -->