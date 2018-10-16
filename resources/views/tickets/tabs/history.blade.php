<div class="row">
    <div class="col-lg-6">
        <div class="h3 text-center">
            История статусов
        </div>
        <table class="table table-hover table-striped">
            <thead>
            <tr>
                <th width="150">
                    Дата, время
                </th>
                <th width="30%">
                    Пользователь
                </th>
                <th>
                    Статус
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach ( $statuses as $status )
                <tr>
                    <td>
                        {{ $status->created_at->format( 'd.m.Y H:i' ) }}
                    </td>
                    <td>
                        {!! $status->author->getName( true ) !!}
                    </td>
                    <td>
                        {{ $status->status_name }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-lg-6">
        <div class="h3 text-center">
            История изменений
        </div>
        <table class="table table-hover table-striped">
            <thead>
            <tr>
                <th width="150">
                    Дата, время
                </th>
                <th width="30%">
                    Пользователь
                </th>
                <th>
                    Текст
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach ( $logs as $log )
                <tr>
                    <td>
                        {{ $log->created_at->format( 'd.m.Y H:i' ) }}
                    </td>
                    <td>
                        {!! $log->author->getName( true ) !!}
                    </td>
                    <td>
                        {{ $log->text }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>