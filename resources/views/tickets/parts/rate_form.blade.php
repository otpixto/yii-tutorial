@if ( $ticketManagement->canRate() )
    {!! Form::open( [ 'url' => route( 'tickets.rate', $ticketManagement->id ), 'id' => 'rate-form' ] ) !!}
    {!! Form::hidden( 'comment', null ) !!}
    {!! Form::hidden( 'rate', null ) !!}
    {!! Form::hidden( 'closed_with_confirm', $closed_with_confirm ?? 0 ) !!}
    <div class="note note-info">
        <dl>
            <dt>
                Оценка работы УО:
            </dt>
            <dd>
                <div class="row margin-bottom-15">
                    <div class="col-lg-2 col-md-2 col-xs-4 margin-top-15">
                        <button type="button" class="btn btn-danger btn-lg btn-block bold" data-rate="1">
                            1
                        </button>
                    </div>
                    <div class="col-lg-2 col-md-2 col-xs-4 margin-top-15">
                        <button type="button" class="btn btn-danger btn-lg btn-block bold" data-rate="2">
                            2
                        </button>
                    </div>
                    <div class="col-lg-2 col-md-2 col-xs-4 margin-top-15">
                        <button type="button" class="btn btn-danger btn-lg btn-block bold" data-rate="3">
                            3
                        </button>
                    </div>
                    <div class="col-lg-2 col-md-2 col-xs-4 margin-top-15">
                        <button type="button" class="btn btn-success btn-lg btn-block bold" data-rate="4">
                            4
                        </button>
                    </div>
                    <div class="col-lg-2 col-md-2 col-xs-4 margin-top-15">
                        <button type="button" class="btn btn-success btn-lg btn-block bold" data-rate="5">
                            5
                        </button>
                    </div>
                    <div class="col-lg-2 col-md-2 col-xs-4 margin-top-15">
                        <button type="button" class="btn btn-warning btn-lg btn-block bold" data-rate="5+">
                            5+
                        </button>
                    </div>
                </div>
            </dd>
        </dl>
    </div>
	{!! Form::close() !!}
@endif