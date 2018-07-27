@extends( 'admin.users.template' )

@section( 'users.content' )

    <div class="row">
        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        Последние действия пользователя
                    </h3>
                </div>
                <div class="panel-body">
                    <table class="table table-hover table-striped">
                        <tr>
                            <th>
                                Дата, время
                            </th>
                            <th>
                                Объект
                            </th>
                            <th>
                                Текст
                            </th>
                        </tr>
                        @foreach ( $userLogsOut as $log )
                            <tr>
                                <td>
                                    {{ $log->created_at->format( 'd.m.Y H:i:s' ) }}
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
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        Последние действия над пользователем
                    </h3>
                </div>
                <div class="panel-body">
                    <table class="table table-hover table-striped">
                        <tr>
                            <th>
                                Дата, время
                            </th>
                            <th>
                                Пользователь
                            </th>
                            <th>
                                Текст
                            </th>
                        </tr>
                        @foreach ( $userLogsIn as $log )
                            <tr>
                                <td>
                                    {{ $log->created_at->format( 'd.m.Y H:i:s' ) }}
                                </td>
                                <td>
                                    @if ( $log->author )
                                        <a href="{{ route( 'users.edit', $log->author->id ) }}">
                                            {{ $log->author->getShortName() }}
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    {{ $log->text }}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection