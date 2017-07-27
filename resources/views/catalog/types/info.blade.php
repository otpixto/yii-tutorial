<div class="panel panel-info">
    <!-- Default panel contents -->
    <div class="panel-heading">
        <h3 class="panel-title">
            Сроки и сезонность
        </h3>
    </div>

    <div class="panel-body form-horizontal">

        <div class="row">

            <div class="col-xs-7 text-right">
                {!! Form::label( null, 'Период на принятие заявки в работу, час', [ 'class' => 'control-label' ] ) !!}
            </div>

            <div class="col-xs-5">
                <span class="form-control">
                    {{ $type->period_acceptance ?? 'Не ограничено' }}
                </span>
            </div>

        </div>

        <div class="row margin-top-10">

            <div class="col-xs-7 text-right">
                {!! Form::label( null, 'Период на исполнение, час', [ 'class' => 'control-label' ] ) !!}
            </div>

            <div class="col-xs-5">
                <span class="form-control">
                    {{ $type->period_execution ?? 'Не ограничено' }}
                </span>
            </div>

        </div>

        <div class="row margin-top-10">

            <div class="col-xs-7 text-right">
                {!! Form::label( null, 'Сезонность устранения', [ 'class' => 'control-label' ] ) !!}
            </div>

            <div class="col-xs-5">
                <span class="form-control">
                    {{ $type->season }}
                </span>
            </div>

        </div>

    </div>
</div>