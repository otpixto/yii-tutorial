<!-- BEGIN USER PROFILE -->
<div class="btn-group-img btn-group">
    <button type="button" class="btn btn-sm md-skip dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
        <span>
            @if ( \Auth::user()->hasRole( 'control' ) )
                <b class="text-info">
                    (Контролирующий)
                </b>
                &nbsp;
            @elseif ( \Auth::user()->hasRole( 'operator' ) )
                <b class="text-info">
                    (Оператор ЕДС)
                </b>
                &nbsp;
            @else ( \Auth::user()->hasRole( 'management' ) && ( \Auth::user()->company || \Auth::user()->managements->count() ) )
                <b class="text-info">
                    ({{ \Auth::user()->company ?? \Auth::user()->managements()->first()->name }})
                </b>
                &nbsp;
            @endif
            {{ \Auth::user()->getShortName() }}
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