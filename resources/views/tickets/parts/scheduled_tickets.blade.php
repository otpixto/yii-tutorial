<table class="table table-hover table-striped">
    @foreach ( $scheduledTicketManagements as $ticketManagement )
        <tr>
            <td>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="row">
                            <div class="col-md-2">
                                <a href="{{ route( 'tickets.show', $ticketManagement->getTicketNumber() ) }}">
                                    <span class="bold">
                                        {{ $ticketManagement->ticket->id }}
                                    </span>
                                    <span class="text-muted small">
                                        /{{ $ticketManagement->id }}
                                    </span>
                                </a>
                            </div>
                            <div class="col-md-5">
                                <div class="bold">
                                    {{ $ticketManagement->ticket->getAddress() }}
                                </div>
                                {{ $ticketManagement->ticket->type->name }}
                            </div>
                            <div class="col-md-5">
                                <div class="bold">
                                    {{ $ticketManagement->executor->getName() }}
                                </div>
                                {{ $ticketManagement->scheduled_begin->format( 'd.m.Y H:i' ) }}
                                -
                                {{ $ticketManagement->scheduled_end->format( 'd.m.Y H:i' ) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6 text-right">
                        {!! Form::open( [ 'url' => route( 'tickets.status', $ticketManagement->getTicketNumber() ), 'data-status' => 'in_process', 'data-id' => $ticketManagement->id, 'class' => 'd-inline submit-loading form-horizontal', 'data-confirm' => 'Вы уверены, что хотите сменить статус на "В работе"?' ] ) !!}
                        {!! Form::hidden( 'model_name', get_class( $ticketManagement ) ) !!}
                        {!! Form::hidden( 'model_id', $ticketManagement->id ) !!}
                        {!! Form::hidden( 'status_code', 'in_process' ) !!}
                        {!! Form::submit( 'В работу', [ 'class' => 'btn btn-success' ] ) !!}
                        {!! Form::close() !!}
                        <button class="btn btn-danger" onClick="postponed( {{ $ticketManagement->ticket->id }} )">
                            Отложить
                        </button>
                        <button class="btn btn-warning" onClick="setExecutor( {{ $ticketManagement->id }} )">
                            Переназначить
                        </button>
                    </div>
                </div>
            </td>
        </tr>
    @endforeach
</table>