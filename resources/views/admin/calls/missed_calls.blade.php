@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.calls.show' ) )


        <div class="row margin-top-15">
            <div class="col-xs-12">

                @if ( $missedCalls->count() )

                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>
                                &nbsp;Номер телефона
                            </th>
                            <th>
                                Кто звонил
                            </th>
                            <th class="text-center">
                                Дата звонка
                            </th>
                            <th>
                                Позвонить
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ( $missedCalls as $call )
                            <tr>
                                <td>
                                    {!! $call->phone !!}
                                </td>
                                <td>
                                    {!! $call->customer ? $call->customer->getName() . ' | ' : '' !!} {!! $call->customer ? $call->customer-> getActualAddress() : '' !!}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse( $call->create_date )->format( 'd.m.Y H:i' ) }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.recall_missed_call', ['id' => $call->id, 'call_id' => 1]) }}"
                                       class="btn btn-sm btn-danger">Позвонить</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                @else
                    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                @endif

            </div>
        </div>

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection
