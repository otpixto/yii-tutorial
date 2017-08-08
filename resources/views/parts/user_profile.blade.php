<!-- BEGIN USER PROFILE -->
<div class="btn-group-img btn-group">
    <button type="button" class="btn btn-sm md-skip dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
        <span>{{ \Auth::user()->getName() }}</span>
        <img src="/assets/layouts/layout5/img/avatar1.jpg" alt=""> </button>
    <ul class="dropdown-menu-v2" role="menu">
        <li>
            <a href="">
                <i class="icon-user"></i>
                Мой профиль
            </a>
        </li>
        <li>
            <a href="">
                <i class="icon-envelope-open"></i>
                Мои сообщения
                <span class="badge badge-danger"> 3 </span>
            </a>
        </li>
        <li class="divider"> </li>
        <li>
            <a href="/logout">
                <i class="icon-key"></i>
                Выход
            </a>
        </li>
    </ul>
</div>
<!-- END USER PROFILE -->