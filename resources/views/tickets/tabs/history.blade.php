<div class="row">
    <div class="col-lg-6">
        <div class="h3 text-center">
            История статусов
        </div>
        <table class="table table-hover table-striped">
            <thead>
            <tr>
                <th>
                    Дата, время
                </th>
                <th>
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
                        @if ( $status->author )
                            {!! $status->author->getShortName() !!}
                        @endif
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
                <th>
                    Дата, время
                </th>
                <th>
                    Пользователь
                </th>
                <th>
                    Объект
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
                        @if ( $log->author )
                            {!! $log->author->getShortName() !!}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if ( $log->parent && isset( $log->parent::$name ) )
                            {{ $log->parent::$name }}
                        @endif
                        <span class="small text-muted">
                        ({{ $log->model_name }})
                    </span>
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