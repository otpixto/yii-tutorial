<div class="panel panel-default">
    <div class="panel-body">

        <div class="row">
            <div class="col-md-2 text-muted small">
                Адрес
            </div>
            <div class="col-md-10 bold">
                {{ $room->getAddress() }}
            </div>
        </div>

        <div class="row margin-top-15">
            <div class="col-md-2 text-muted small">
                Подъезд
            </div>
            <div class="col-md-2 bold">
                {{ $room->porch }}
            </div>
            <div class="col-md-2 text-muted small">
                Этаж
            </div>
            <div class="col-md-2 bold">
                {{ $room->floor }}
            </div>
            <div class="col-md-2 text-muted small">
                Номер
            </div>
            <div class="col-md-2 bold">
                {{ $room->number }}
            </div>
        </div>

    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            Заявители
            <span class="badge">
                {{ $customers->count() }}
            </span>
        </h3>
    </div>
    <div class="panel-body">
        @if ( $customers->count() )
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>
                            ФИО
                        </th>
                        <td>
                            Телефон(ы)
                        </td>
                        @if ( \Auth::user()->can( 'tickets.show' ) )
                            <td>
                                Заявки
                            </td>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @foreach ( $customers as $customer )
                    <tr>
                        <td>
                            {{ $customer->getName() }}
                        </td>
                        <td>
                            {{ $customer->getPhones() }}
                        </td>
                        @if ( \Auth::user()->can( 'tickets.show' ) )
                            <td class="text-center">
                                <a href="{{ route( 'tickets.index', [ 'phone' => $customer->phone ] ) }}" class="badge badge-{{ $customer->getPhoneTickets()->count() ? 'info' : 'default' }} bold">
                                    {{ $customer->getPhoneTickets()->count() }}
                                </a>
                            </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            @include( 'parts.error', [ 'error' => 'Заявители отсутствуют' ] )
        @endif
    </div>
</div>