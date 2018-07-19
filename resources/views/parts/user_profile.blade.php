<!-- BEGIN USER PROFILE -->
<div class="btn-group-img btn-group">
    <button type="button" class="btn btn-sm md-skip dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
        <span>
            {{ \Auth::user()->getShortName( true ) }}
        </span>
    </button>
    <ul class="dropdown-menu-v2" role="menu">
        <li class="hidden">
            <a href="">
                <i class="icon-user"></i>
                Мой профиль
            </a>
        </li>
        <li class="divider hidden"> </li>
        <li>
            <a href="/logout">
                <i class="icon-key"></i>
                Выход
            </a>
        </li>
    </ul>
</div>
<!-- END USER PROFILE -->