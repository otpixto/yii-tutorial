@if ( ! $ticketManagement->rate )
    {!! Form::open( [ 'url' => route( 'tickets.rate' ), 'id' => 'rate-form' ] ) !!}
    {!! Form::hidden( 'id', $ticketManagement->id ) !!}
    {!! Form::hidden( 'comment', null ) !!}
    {!! Form::hidden( 'rate', null ) !!}
    {!! Form::close() !!}
    <div class="note note-info">
        <dl>
            <dt>
                Оценка работы ЭО:
            </dt>
            <dd>
                <button type="button" class="btn btn-danger btn-lg bold" data-rate="1">
                    1
                </button>
                <button type="button" class="btn btn-danger btn-lg bold" data-rate="2">
                    2
                </button>
                <button type="button" class="btn btn-danger btn-lg bold" data-rate="3">
                    3
                </button>
                <button type="button" class="btn btn-success btn-lg bold" data-rate="4">
                    4
                </button>
                <button type="button" class="btn btn-success btn-lg bold" data-rate="5">
                    5
                </button>
            </dd>
        </dl>
    </div>
@endif