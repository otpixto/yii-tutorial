<h4 class="block">
    Выберите исполнителей:
</h4>
<table class="table table-hover table-striped table-bordered">
    <thead>
        <tr class="warning">
            <th width="30" class="text-center">
                <input type="checkbox" checked="checked" class="hidden" />
            </th>
            <th>
                Наименование
            </th>
            <th>
                Адрес
            </th>
            <th>
                Телефон
            </th>
        </tr>
    </thead>
    <tbody>
    @foreach ( $managements as $management )
        <tr>
            <td class="text-center">
                <input type="checkbox" name="managements[]" id="management-{{ $management->id }}" value="{{ $management->id }}" checked="checked" />
            </td>
            <td>
                <label for="management-{{ $management->id }}" class="bold">
                    {{ $management->name }}
                </label>
            </td>
            <td>
                {{ $management->address }}
            </td>
            <td>
                {{ $management->phone }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>