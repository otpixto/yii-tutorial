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
                Адрес проживания
            </th>
            <th>
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
                {{ $customer->getPhones() }}
            </td>
            <td>
                <small>
                    {{ $customer->getAddress() }}
                </small>
            </td>
            <td class="text-right">
                <button type="button" class="btn btn-info" data-id="{{ $customer->id }}" data-firstname="{{ $customer->firstname }}" data-middlename="{{ $customer->middlename }}" data-lastname="{{ $customer->lastname }}" data-phone="{{ $customer->phone }}" data-phone2="{{ $customer->phone2 }}" data-address="{{ $customer->actualAddress->name ?? null }}" data-address-id="{{ $customer->actualAddress->id ?? null }}" data-flat="{{ $customer->actual_flat }}" data-action="customers-select">
                    <i class="glyphicon glyphicon-ok"></i>
                </button>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>