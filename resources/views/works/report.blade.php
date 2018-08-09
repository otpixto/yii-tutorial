<style>
    th, td {
        border: 1px solid #000;
        vertical-align: middle;
    }
    .text-center {
        text-align: center;
    }
</style>
<table>
    <thead>
        <tr>
            <th>
                Фильтр
            </th>
            <th colspan="11">
                <div>
                    {!! implode( '</div>;<div>', $filters ) !!}
                </div>
            </th>
        </tr>
        <tr>
            <th>
                Ресурс отключения
            </th>
            <th>
                Кол-во домов
            </th>
            <th>
                Кол-во квартир
            </th>
            <th>
                Адреса
            </th>
            <th>
                Аварийная ситуация/ плановые работы
            </th>
            <th>
                Дата и время отключения
            </th>
            <th>
                Предельные сроки устранения
            </th>
            <th>
                Дата и время окончания
            </th>
            <th>
                Описание работ
            </th>
            <th>
                Кто проводит работы
            </th>
            <th>
                Ответственный за проведение работ, телефон
            </th>
            <th>
                Примечание (меры помощи жителям)
            </th>
        </tr>
    </thead>
    <tbody>
    @php
        $last_category_id = null;
    @endphp
    @foreach ( $categories as $category )
        <tr style="background-color: {{ $category->color }};">
            <th>
                Итого по {{ $category->name }}
            </th>
            <th class="text-center">
                {{ $data[ $category->id ][ 'totals' ][ 'buildings' ] }}
            </th>
            <th class="text-center">
                {{ $data[ $category->id ][ 'totals' ][ 'flats' ] }}
            </th>
            <th colspan="9">
                &nbsp;
            </th>
        </tr>
        @if ( count( $data[ $category->id ][ 'list' ] ) )
            @foreach ( $data[ $category->id ][ 'list' ] as $work )
                <tr style="background-color: {{ $category->color }};">
                    @if ( $category->id != $last_category_id )
                        <td rowspan="{{ count( $data[ $category->id ][ 'list' ] ) }}">
                            {{ $category->name }}
                        </td>
                        @php
                            $last_category_id = $category->id;
                        @endphp
                    @else
                        <td>
                            &nbsp;
                        </td>
                    @endif
                    <td class="text-center">
                        {{ $work[ 'count_buildings' ] ?? 0 }}
                    </td>
                    <td class="text-center">
                        {{ $work[ 'count_flats' ] ?? 0 }}
                    </td>
                    <td>
                        @foreach ( $work[ 'addresses' ] as $address )
                            <div>
                                {{ $address }}
                            </div>
                        @endforeach
                    </td>
                    <td>
                        &nbsp;
                    </td>
                    <td>
                        {{ $work[ 'time_begin' ]->format( 'd.m.Y H:i' ) }}
                    </td>
                    <td class="text-center">
                        &nbsp;
                    </td>
                    <td>
                        {{ $work[ 'time_end' ]->format( 'd.m.Y H:i' ) }}
                    </td>
                    <td>
                        {{ $work[ 'composition' ] }}
                    </td>
                    <td>
                        {{ $work[ 'management' ] }}
                    </td>
                    <td>
                        @if ( $work[ 'executor_name' ] )
                            {{ $work[ 'executor_name' ] }}
                            @if ( $work[ 'executor_phone' ] )
                                <div>
                                    Тел. {{ $work[ 'executor_phone' ] }}
                                </div>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        &nbsp;
                    </td>
                </tr>
            @endforeach
        @else
            <tr style="background-color: {{ $category->color }};">
                <td colspan="12">
                    Отключения отсутствуют
                </td>
            </tr>
        @endif
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th class="text-center">
                ИТОГО
            </th>
            <th class="text-center">
                {{ $totals[ 'buildings' ] }}
            </th>
            <th class="text-center">
                {{ $totals[ 'flats' ] }}
            </th>
        </tr>
    </tfoot>
</table>

{{--{{ dd( time() ) }}--}}