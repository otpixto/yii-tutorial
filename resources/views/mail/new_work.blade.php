<h3>Уважаемый(ая) {{ $user->firstname }} {{ $user->middlename }}.</h3>

<p>Уведомляем Вас о том, что в связи с проведением работ на сетях по адресу {{ $user->customer->actualBuilding->name ?? '' }}

    в период с {{ \Carbon\Carbon::parse( $work->time_begin )->format( 'd.m.Y H:i' ) }}
    по {{ \Carbon\Carbon::parse( $work->time_end )->format( 'd.m.Y H:i' ) }}

    будет отсутствовать ресурс {{ $work->category->parent->name ?? '' }} {{ $work->category->name ?? '' }}.</p>

<p>Исполнитель работ:</p>

<ul>
    @foreach ( $managements as $management )
        @if ( $management->parent )
            <li>{{ $management->parent->name ?? '' }}</li>
        @endif
        <li>{{ $management->name ?? '' }}</li>
    @endforeach
</ul>

<p>&nbsp;</p>
<p>С уважением и заботой о Вас</p>

<p>команда "ЕДС-регион".</p>

<p>Вы получили это письмо, так как дали свое согласие на получение рассылки от АИС «ЕДС-регион».</p>

<p>Если вы хотите отказаться от подписки на наш список рассылок, то перейдите по <a href="{{ route('profile.unsubscribe', ['user_id' => $user->id]) }}">ссылке</a>.</p>