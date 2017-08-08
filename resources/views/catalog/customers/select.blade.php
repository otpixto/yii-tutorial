<table class="table table-hover table-striped">
    <thead>
        <tr>
            <th>
                &nbsp;
            </th>
            <th>
                ФИО
            </th>
            <th>
                Телефон(ы)
            </th>
            <th>
                Адрес проживания
            </th>
        </tr>
    </thead>
    <tbody>
    @foreach ( $customers as $customer )
        <tr>
            <td>
                <button type="button" class="btn btn-info" data-firstname="{{ $customer->firstname }}" data-middlename="{{ $customer->middlename }}" data-lastname="{{ $customer->lastname }}" data-phone="{{ $customer->phone }}" data-phone2="{{ $customer->phone2 }}" data-address="{{ $customer->address->name ?? null }}" data-address-id="{{ $customer->address_id ?? null }}" data-action="customers-select">
                    <i class="glyphicon glyphicon-ok"></i>
                </button>
            </td>
            <td>
                {{ $customer->getName() }}
            </td>
            <td>
                {{ $customer->getPhones() }}
            </td>
            <td>
                {{ $customer->address->name ?? '-' }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>