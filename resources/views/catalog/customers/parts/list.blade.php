@if ( $customers->count() )

    <div class="row">
        <div class="col-md-8">
            {{ $customers->render() }}
        </div>
        <div class="col-md-4 text-right margin-top-10 margin-bottom-10">
            <span class="label label-info">
                Найдено: <b>{{ $customers->total() }}</b>
            </span>
        </div>
    </div>

    <table class="table table-hover table-striped">
        <thead>
        <tr>
            <th>
                ФИО
            </th>
            <th>
                Телефон(ы)
            </th>
            <th>
                Адрес
            </th>
            @if ( \Auth::user()->can( 'tickets.show' ) )
                <th class="text-center">
                    Заявки
                </th>
            @endif
            <th class="text-center">
                ЛК
            </th>
            <th class="text-right">
                &nbsp;
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach ( $customers as $customer )
            <tr>
                <td>
                    {{ $customer->getName() }}
                </td>
                <td>
                    <span class="small">
                        {{ $customer->getPhones() }}
                    </span>
                </td>
                <td>
                    <span class="small">
                        {{ $customer->getActualAddress() }}
                    </span>
                </td>
                @if ( \Auth::user()->can( 'tickets.show' ) )
                    <td class="text-center">
                        <a href="{{ route( 'tickets.index', [ 'phone' => $customer->phone ] ) }}" class="badge badge-{{ $customer->tickets()->mine()->count() ? 'info' : 'default' }} bold">
                            {{ $customer->tickets()->mine()->count() }}
                        </a>
                    </td>
                @endif
                <td class="text-center">
                    <a href="javascript:;" data-customer-lk="{{ $customer->id }}">
                        @if ( $customer->user && $customer->user->isActive() )
                            @include( 'parts.yes' )
                        @else
                            @include( 'parts.no' )
                        @endif
                    </a>
                </td>
                <td class="text-right">
                    @if ( \Auth::user()->can( 'catalog.customers.edit' ) )
                        <a href="{{ route( 'customers.edit', $customer->id ) }}" class="btn btn-info">
                            <i class="fa fa-edit"></i>
                        </a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $customers->render() }}

@else
    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
@endif