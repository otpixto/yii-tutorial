{!! Form::model( $ticketManagement, [ 'method' => 'put', 'route' => [ 'tickets.managements.save', $ticketManagement->id ], 'class' => 'form-horizontal submit-loading ajax' ] ) !!}
<table class="table table-hover table-striped table-condensed">
    <thead>
    <tr>
        <th>

        </th>
        <th>
            УО
        </th>
        <th>
            Контакты
        </th>
    </tr>
    </thead>
    <tbody>
    @foreach ( $managements as $management )
        <tr>
            <td class="text-right md-checkbox-inline">
                <div class="md-radio">
                    {!! Form::radio( 'management_id', $management->id, ( $management->id == $ticketManagement->management_id ), [ 'class' => 'md-radiobtn', 'id' => 'management-' . $management->id ] ) !!}
                    <label for="management-{{ $management->id }}">
                        <span class="inc"></span>
                        <span class="check"></span>
                        <span class="box"></span>
                    </label>
                </div>
            </td>
            <td>
                <label for="management-{{ $management->id }}">
                    @if ( $management->parent )
                        <div class="text-muted">
                            {{ $management->parent->name }}
                        </div>
                    @endif
                    <div>
                        {{ $management->name }}
                    </div>
                </label>
                @if ( ! $management->has_contract )
                    <div class="label label-danger bold">
                        Отсутствует договор
                    </div>
                @endif
            </td>
            <td>
                {!! $management->getPhones() !!}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
{!! Form::close() !!}