<div class="note note-info">
    <div class="row">
        <div class="col-xs-4 text-center">
            Занято:
            <span class="badge badge-info bold">
                {{ $states[ 'busy' ] }}
            </span>
        </div>
        <div class="col-xs-4 text-center">
            Всего:
            <span class="badge badge-info bold">
                {{ $states[ 'count' ] }}
            </span>
        </div>
        <div class="col-xs-4 text-center">
            Ожидают:
            <span class="badge badge-info bold">
                {{ $states[ 'callers' ] }}
            </span>
        </div>
    </div>
</div>
<table class="table table-hover table-striped">
    <thead>
        <tr>
            <th>
                Номер
            </th>
            <th>
                Оператор
            </th>
            <th class="text-center">
                Свободен
            </th>
            <th>
                Интерфейс
            </th>
        </tr>
    </thead>
    <tbody>
    @foreach ( $states[ 'list' ] as $channel => $state )
        <tr>
            <td>
                <span class="bold">
                    {{ $state[ 'number' ] }}
                </span>
            </td>
            <td>
                @if ( isset( $state[ 'operator' ] ) )
                    {{ $state[ 'operator' ]->getShortName() }}
                @else
                    -
                @endif
            </td>
            <td class="text-center">
                @if ( $state[ 'isFree' ] )
                    @include( 'parts.yes' )
                @else
                    @include( 'parts.no' )
                @endif
            </td>
            <td>
                <span class="text-muted small">
                    {{ $channel }}
                </span>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>