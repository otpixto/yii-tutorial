@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Обращения', route( 'tickets.index' ) ],
        [ 'Обращение #' . $ticket->id ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row">
        <div class="col-lg-6">

            @if ( $ticket->status_code == 'closed_with_confirm' && ! $ticket->rate )
                <div class="row">
                    <div class="col-xs-12">
                        {!! Form::open( [ 'url' => route( 'tickets.rate', $ticket->id ), 'id' => 'rate-form' ] ) !!}
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
                    </div>
                </div>
            @endif

            @if ( $ticket->getAvailableStatuses() )
                <div class="row">
                    <div class="col-xs-12">
                        <div class="note note-info">
                            <dl>
                                <dt>Сменить статус:</dt>
                                <dd>
                                    @foreach( $ticket->getAvailableStatuses() as $status_code => $status_name )
                                        {!! Form::open( [ 'url' => route( 'tickets.status', $ticket->id ), 'class' => 'd-inline submit-loading form-horizontal' ] ) !!}
                                        {!! Form::hidden( 'status_code', $status_code ) !!}
                                        {!! Form::submit( $status_name, [ 'class' => 'btn btn-primary btn-lg' ] ) !!}
                                        {!! Form::close() !!}
                                    @endforeach
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-xs-6">
                    <div class="note note-{{ $ticket->getClass() }}">
                        <dl>
                            <dt>Статус:</dt>
                            <dd>{{ $ticket->status_name }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="note">
                        @if ( $ticket->canEdit() )
                            <button type="button" class="btn btn-lg btn-default pull-left margin-right-10 hidden-print">
                                <i class="fa fa-edit"></i>
                            </button>
                        @endif
                        <dl>
                            <dt>Тип обращения:</dt>
                            <dd>{{ $ticket->type->name }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-6">
                    <div class="note">
                        @if ( $ticket->canEdit() )
                            <button type="button" class="btn btn-lg btn-default pull-left margin-right-10 hidden-print">
                                <i class="fa fa-edit"></i>
                            </button>
                        @endif
                        <dl>
                            <dt>Адрес проблемы:</dt>
                            <dd>{{ $ticket->address }}</dd>
                        </dl>
                    </div>
                </div>
				<div class="col-xs-6">
                    <div class="note">
                        @if ( $ticket->canEdit() )
                            <button type="button" class="btn btn-lg btn-default pull-left margin-right-10 hidden-print">
                                <i class="fa fa-edit"></i>
                            </button>
                        @endif
                        <dl>
                            <dt>Проблемное место:</dt>
                            <dd>{{ $ticket->place }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="row">
				<div class="col-xs-6">
                    <div class="note">
                        @if ( $ticket->canEdit() )
                            <button type="button" class="btn btn-lg btn-default pull-left margin-right-10 hidden-print">
                                <i class="fa fa-edit"></i>
                            </button>
                        @endif
                        <dl>
                            <dt>Текст обращения:</dt>
                            <dd>{{ $ticket->text }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="note">
                        @if ( $ticket->canEdit() )
                            <button type="button" class="btn btn-lg btn-default pull-left margin-right-10 hidden-print">
                                <i class="fa fa-edit"></i>
                            </button>
                        @endif
                        <dl>
                            <dt>Дополнительные метки:</dt>
                            <dd>
                                @if ( $ticket->type->is_pay || $ticket->type->category->is_pay )
                                    <span class="badge badge-warning bold">
                                        Платно
                                    </span>
                                    &nbsp;
                                @endif
                                @if ( $ticket->emergency )
                                    <span class="badge badge-danger bold">
                                        Авария
                                    </span>
                                    &nbsp;
                                @endif
                                @if ( $ticket->urgently )
                                    <span class="badge badge-danger bold">
                                        Срочно
                                    </span>
                                    &nbsp;
                                @endif
                                @if ( $ticket->dobrodel )
                                    <span class="badge badge-danger bold">
                                        Добродел
                                    </span>
                                @endif
                                @if ( $ticket->group_uuid )
                                    <a href="{{ route( 'tickets.index' ) }}?group={{ $ticket->group_uuid }}" class="badge badge-info bold">
                                        Сгруппировано
                                    </a>
                                    &nbsp;
                                @endif
                                &nbsp;
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
			
            <hr />

            <div class="row">
                <div class="col-xs-12">
                    <div class="note">
                        <strong>Сезонность устранения: </strong>
                        {{ $ticket->type->season }}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <div class="note">
                        <dl>
                            <dt>Период на принятие заявки в работу, час:</dt>
                            <dd>{{ $ticket->type->period_acceptance }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="note">
                        <dl>
                            <dt>Период на исполнение, час:</dt>
                            <dd>{{ $ticket->type->period_execution }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            @if ( $dt_transferred )
                <div class="row">
                    <div class="col-xs-12">
                        <div class="note">
                            <strong>Заявка передана ЭО: </strong>
                            {{ $dt_transferred->format( 'd.m.Y H:i' ) }}
                        </div>
                    </div>
                </div>
            @endif

            @if ( $dt_acceptance_expire && $dt_execution_expire )
                <div class="row">
                    <div class="col-xs-6">
                        @if ( $dt_accepted )
                            <div class="note note-{{ $dt_accepted->timestamp > $dt_acceptance_expire->timestamp ? 'danger' : 'success' }}">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <dl>
                                            <dt>Принять до:</dt>
                                            <dd>
                                                {{ $dt_acceptance_expire->format( 'd.m.Y H:i' ) }}
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="col-xs-6">
                                        <dl>
                                            <dt>Принято:</dt>
                                            <dd>
                                                {{ $dt_accepted->format( 'd.m.Y H:i' ) }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                                @if ( $dt_accepted->timestamp > $dt_acceptance_expire->timestamp )
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <span class="badge badge-danger">
                                                <i class="fa fa-warning"></i>
                                                Просрочено
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="note {{ $dt_now->timestamp > $dt_acceptance_expire->timestamp ? 'note-danger' : '' }}">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <dl>
                                            <dt>Принять до:</dt>
                                            <dd>
                                                {{ $dt_acceptance_expire->format( 'd.m.Y H:i' ) }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                                @if ( $dt_now->timestamp > $dt_acceptance_expire->timestamp )
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <span class="badge badge-danger">
                                                <i class="fa fa-warning"></i>
                                                Просрочено
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="col-xs-6">
                        @if ( $dt_completed )
                            <div class="note note-{{ $dt_completed->timestamp > $dt_execution_expire->timestamp ? 'danger' : 'success' }}">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <dl>
                                            <dt>Выполнить до:</dt>
                                            <dd>
                                                {{ $dt_execution_expire->format( 'd.m.Y H:i' ) }}
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="col-xs-6">
                                        <dl>
                                            <dt>Выполнено:</dt>
                                            <dd>
                                                {{ $dt_completed->format( 'd.m.Y H:i' ) }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                                @if ( $dt_completed->timestamp > $dt_execution_expire->timestamp )
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <span class="badge badge-danger">
                                                <i class="fa fa-warning"></i>
                                                Просрочено
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="note {{ $dt_now->timestamp > $dt_execution_expire->timestamp ? 'note-danger' : '' }}">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <dl>
                                            <dt>Выполнить до:</dt>
                                            <dd>
                                                {{ $dt_execution_expire->format( 'd.m.Y H:i' ) }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                                @if ( $dt_now->timestamp > $dt_execution_expire->timestamp )
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <span class="badge badge-danger">
                                                <i class="fa fa-warning"></i>
                                                Просрочено
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            @endif

            @if ( $execution_hours )
                <div class="row">
                    <div class="col-xs-12">
                        <div class="note note-info">
                            <b>Продолжительность работы ЭО в часах: </b>
                            {{ $execution_hours }}
                        </div>
                    </div>
                </div>
            @endif

            @if ( $ticket->rate )
                <div class="row">
                    <div class="col-xs-12">
                        <div class="note">
                            <b>Оценка работы ЭО: </b>
                            @include( 'parts.rate', [ 'ticket' => $ticket ] )
                            @if ( $ticket->rate_comment )
                                <p>
                                    {{ $ticket->rate_comment }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

        </div>
        <div class="col-lg-6">

            <div class="row">
                <div class="col-xs-6">
                    <div class="note">
                        @if ( $ticket->status_code == 'draft' )
                            <button type="button" class="btn btn-lg btn-default pull-left margin-right-10 hidden-print">
                                <i class="fa fa-edit"></i>
                            </button>
                        @endif
                        <dl>
                            <dt>ФИО Заявителя:</dt>
                            <dd>{{ $ticket->getName() }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="note">
                        @if ( $ticket->status_code == 'draft' )
                            <button type="button" class="btn btn-lg btn-default pull-left margin-right-10 hidden-print">
                                <i class="fa fa-edit"></i>
                            </button>
                        @endif
                        <dl>
                            <dt>Адрес проживания:</dt>
                            <dd>{{ $ticket->actual_address ?? '-' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <hr />

            @if ( $ticket->type->need_act )
                <div class="alert alert-warning">
                    <i class="glyphicon glyphicon-exclamation-sign"></i>
                    Требуется Акт выполненных работ
                </div>
            @endif

            <div class="row">
                <div class="col-xs-12">
                    <div class="note">
                        <h4>Эксплуатационные организации</h4>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        Наименование \ Телефон \ Адрес
                                    </th>
                                    <th>
                                        Ответственный
                                    </th>
                                    <th>
                                        Статус
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ( $ticket->managements as $ticketManagement )
                                <tr>
                                    <td>
                                        <dl>
                                            <dt>
                                                {{ $ticketManagement->management->name }}
                                            </dt>
                                            <dd>
                                                {{ $ticketManagement->management->phone }}
                                            </dd>
                                            <dd class="small">
                                                {{ $ticketManagement->management->address }}
                                            </dd>
                                        </dl>
                                        <p class="margin-top-10 hidden-print">
                                            <a href="{{ route( 'tickets.act', $ticketManagement->id ) }}" class="btn btn-info">
                                                <i class="glyphicon glyphicon-print"></i>
                                                Акт выполненных работ
                                            </a>
                                        </p>
                                    </td>
                                    <td>
                                        -
                                    </td>
                                    <td>
                                        <span class="text-{{ $ticketManagement->getClass() }}">
                                            {{ $ticketManagement->status_name }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <div class="note">
                        <h4>Комментарии</h4>
                        @if ( $ticket->comments->count() )
                            @include( 'parts.comments', [ 'ticket' => $ticket, 'comments' => $ticket->comments ] )
                        @endif
                    </div>
                </div>
            </div>

            @if ( $ticket->canComment() )
                <div class="row">
                    <div class="col-xs-12">
                        <button type="button" class="btn btn-block btn-primary btn-lg" data-action="comment" data-model-name="{{ get_class( $ticket ) }}" data-model-id="{{ $ticket->id }}" data-file="1">
                            <i class="fa fa-commenting"></i>
                            Добавить комментарий
                        </button>
                    </div>
                </div>
            @endif

        </div>
    </div>

@endsection

@section( 'css' )
    <style>
        dl, .alert {
            margin: 0px;
        }
        .note {
            margin: 5px 0;
        }
        .d-inline {
            display: inline;
        }
    </style>
@endsection

@section( 'js' )

    <script src="/assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>

    <script type="text/javascript">

        $( document )

            .on( 'click', '[data-rate]', function ( e )
            {

                e.preventDefault();

                var rate = $( this ).attr( 'data-rate' );
                var form = $( '#rate-form' );

                form.find( '[name="rate"]' ).val( rate );

                if ( rate < 4 )
                {
                    bootbox.prompt({
                        title: 'Введите комментарий к оценке',
                        inputType: 'textarea',
                        callback: function (result) {
                            if ( !result ) {
                                alert('Действие отменено!');
                            }
                            else {
                                form.find('[name="comment"]').val(result);
                                form.submit();
                            }
                        }
                    });
                }
                else
                {
                    form.submit();
                }

            });

    </script>

@endsection