<div class="row">
    <div class="col-xs-12">
        <div class="note note-info">
            <h4>Выполненные работы</h4>
            <div class="row margin-bottom-10">
                <label class="col-xs-5 control-label text-muted">Наименование</label>
                <label class="col-xs-2 control-label text-muted text-right">Кол-во</label>
                <label class="col-xs-2 control-label text-muted">Е.И.</label>
                <label class="col-xs-2 control-label text-muted text-right">Стоимость</label>
            </div>
            @if ( \Auth::user()->can( 'tickets.services.edit' ) )
                {!! Form::model( $ticketManagement, [ 'method' => 'put', 'route' => [ 'tickets.services.save', $ticketManagement->id ], 'class' => 'submit-loading' ] ) !!}
                <div class="mt-repeater" id="ticket-services">
                    <div data-repeater-list="services">
                        @if ( $ticketManagement->services->count() )
                            @foreach ( $ticketManagement->services as $service )
                                <div data-repeater-item="" class="row margin-bottom-10">
                                    <div class="col-xs-5">
                                        {!! Form::hidden( 'id', $service->id ) !!}
                                        {!! Form::text( 'name', $service->name, [ 'class' => 'form-control', 'placeholder' => 'Наименование', 'required' ] ) !!}
                                    </div>
                                    <div class="col-xs-2">
                                        {!! Form::text( 'quantity', $service->quantity, [ 'class' => 'form-control calc-totals quantity text-right', 'placeholder' => 'Кол-во', 'required' ] ) !!}
                                    </div>
                                    <div class="col-xs-2">
                                        {!! Form::text( 'unit', $service->unit, [ 'class' => 'form-control', 'required' ] ) !!}
                                    </div>
                                    <div class="col-xs-2">
                                        {!! Form::text( 'amount', $service->amount ?? null, [ 'class' => 'form-control calc-totals amount text-right', 'placeholder' => 'Стоимость', 'required' ] ) !!}
                                    </div>
                                    <div class="col-xs-1 text-right hidden-print">
                                        <button type="button" data-repeater-delete="" class="btn btn-danger">
                                            <i class="fa fa-close"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div data-repeater-item="" class="row margin-bottom-10 hidden-print">
                                <div class="col-xs-5">
                                    {!! Form::hidden( 'id', '' ) !!}
                                    {!! Form::text( 'name', '', [ 'class' => 'form-control', 'placeholder' => 'Наименование', 'required' ] ) !!}
                                </div>
                                <div class="col-xs-2">
                                    {!! Form::text( 'quantity', 1, [ 'class' => 'form-control calc-totals quantity text-right', 'placeholder' => 'Кол-во', 'required' ] ) !!}
                                </div>
                                <div class="col-xs-2">
                                    {!! Form::text( 'unit', 'шт', [ 'class' => 'form-control', 'required' ] ) !!}
                                </div>
                                <div class="col-xs-2">
                                    {!! Form::text( 'amount', '', [ 'class' => 'form-control calc-totals amount text-right', 'placeholder' => 'Стоимость', 'required' ] ) !!}
                                </div>
                                <div class="col-xs-1 text-right hidden-print">
                                    <button type="button" data-repeater-delete="" class="btn btn-danger">
                                        <i class="fa fa-close"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="row margin-bottom-10 bg-info">
                        <div class="col-xs-9 text-right bold">
                            Итого:
                        </div>
                        <div class="col-xs-2 text-right bold" id="ticket-services-total">
                            {{ number_format( $ticketManagement->services->sum( function ( $service ){ return $service[ 'amount' ] * $service[ 'quantity' ]; } ), 2, '.', '' ) }}
                        </div>
                    </div>
                    <hr class="hidden-print" />
                    <div class="row hidden-print">
                        <div class="col-xs-6">
                            <button type="button" data-repeater-create="" class="btn btn-sm btn-default mt-repeater-add">
                                <i class="fa fa-plus"></i>
                                Добавить
                            </button>
                        </div>
                        <div class="col-xs-6 text-right">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check"></i>
                                Сохранить
                            </button>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            @else
                @if ( $ticketManagement->services->count() )
                    @foreach ( $ticketManagement->services as $service )
                        <div class="row margin-bottom-10">
                            <div class="col-xs-6">
                                {{ $service->name }}
                            </div>
                            <div class="col-xs-2 text-right">
                                {{ $service->quantity }}
                            </div>
                            <div class="col-xs-2">
                                {{ $service->unit }}
                            </div>
                            <div class="col-xs-2 text-right">
                                {{ $service->amount }}
                            </div>
                        </div>
                        <hr />
                    @endforeach
                @else
                    <div class="small text-danger">Выполненных работ нет</div>
                @endif
            @endif
        </div>
    </div>
</div>