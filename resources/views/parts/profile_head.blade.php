<!-- BEGIN USER LOGIN DROPDOWN -->
<li class="dropdown dropdown-user dropdown-dark">
    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
        <img alt="" class="img-circle" src="/assets/layouts/layout3/img/avatar9.jpg">
        <span class="username username-hide-mobile">{{ Auth::user()->email }}</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-default">
        <li>
            <a href="">
                <i class="icon-user"></i> Мой профиль</a>
        </li>
        <li>
            <a href="">
                <i class="icon-calendar"></i> Мои заявки</a>
        </li>
        <li>
            <a href="">
                <i class="icon-envelope-open"></i> Мои сообщения
                <span class="badge badge-danger"> 3 </span>
            </a>
        </li>
        <li>
            <a href="">
                <i class="icon-rocket"></i> Мои задачи
                <span class="badge badge-success"> 7 </span>
            </a>
        </li>
        <li class="divider"> </li>
        <li>
            <a href="/lock">
                <i class="icon-lock"></i> Заблокировать</a>
        </li>
        <li>
            <a href="/logout">
                <i class="icon-key"></i> Выйти</a>
        </li>
    </ul>
</li>
<!-- END USER LOGIN DROPDOWN -->