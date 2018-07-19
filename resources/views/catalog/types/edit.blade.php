@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Классификатор', route( 'types.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.types.edit' ) )

        <div class="panel panel-default">
            <div class="panel-body">

                {!! Form::model( $type, [ 'method' => 'put', 'route' => [ 'types.update', $type->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                <div class="form-group">

                    <div class="col-md-6">
                        {!! Form::label( 'category_id', 'Категория', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'category_id', $categories, \Input::old( 'category_id', $type->category_id ), [ 'class' => 'form-control select2', 'placeholder' => 'Категория' ] ) !!}
                    </div>

                    <div class="col-md-6">
                        {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'name', \Input::old( 'name', $type->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
                    </div>

                </div>

                <div class="form-group">

                    <div class="col-md-12">
                        {!! Form::label( 'description', 'Подсказки', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::textarea( 'description', \Input::old( 'description', $type->description ), [ 'class' => 'form-control', 'placeholder' => 'Подсказки' ] ) !!}
                    </div>

                </div>

                <div class="form-group">
                    <div class="col-xs-6">
                        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                    <div class="col-xs-6 text-right">
                        <a href="{{ route( 'types.managements', $type->id ) }}" class="btn btn-default btn-circle">
                            УО
                            <span class="badge">{{ $typeManagementsCount }}</span>
                        </a>
                    </div>
                </div>

                {!! Form::close() !!}

            </div>

        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Сроки и сезонность</h3>
            </div>
            <div class="panel-body">

                {!! Form::model( $type, [ 'method' => 'put', 'route' => [ 'types.update', $type->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                <div class="form-group">

                    <div class="col-md-3">
                        {!! Form::label( 'period_acceptance', 'Период на принятие заявки в работу, час', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::number( 'period_acceptance', \Input::old( 'period_acceptance', $type->period_acceptance ), [ 'class' => 'form-control', 'placeholder' => 'Период на принятие заявки в работу, час', 'step' => 0.1, 'min' => 0 ] ) !!}
                    </div>

                    <div class="col-md-3">
                        {!! Form::label( 'period_execution', 'Период на исполнение, час', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::number( 'period_execution', \Input::old( 'period_execution', $type->period_execution ), [ 'class' => 'form-control', 'placeholder' => 'Период на исполнение, час', 'step' => 0.1, 'min' => 0 ] ) !!}
                    </div>

                    <div class="col-md-6">
                        {!! Form::label( 'season', 'Сезонность устранения', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'season', \Input::old( 'season', $type->season ), [ 'class' => 'form-control', 'placeholder' => 'Сезонность устранения' ] ) !!}
                    </div>

                </div>

                <div class="form-group hidden-print">
                    <div class="col-xs-12">
                        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                </div>

                {!! Form::close() !!}

            </div>

        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">АИС ГЖИ</h3>
                    </div>
                    <div class="panel-body">

                        {!! Form::model( $type, [ 'method' => 'put', 'route' => [ 'types.update', $type->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                        <div class="form-group">

                            <div class="col-xs-12">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        GUID
                                    </span>
                                    {!! Form::text( 'guid', \Input::old( 'guid', $type->guid ), [ 'class' => 'form-control', 'placeholder' => 'GUID' ] ) !!}
                                </div>
                            </div>

                        </div>

                        <div class="form-group hidden-print">
                            <div class="col-xs-12">
                                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                            </div>
                        </div>

                        {!! Form::close() !!}

                    </div>

                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Настройки</h3>
                    </div>
                    <div class="panel-body">

                        {!! Form::model( $type, [ 'method' => 'put', 'route' => [ 'types.update', $type->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                        {!! Form::hidden( 'checkboxes', 1 ) !!}

                        <div class="form-group">
                            <div class="col-xs-12">
                                <div class="mt-checkbox-list">
                                    <label class="mt-checkbox mt-checkbox-outline">
                                        Требуется акт
                                        {!! Form::checkbox( 'need_act', 1, \Input::old( 'need_act', $type->need_act ) ) !!}
                                        <span></span>
                                    </label>
                                    <label class="mt-checkbox mt-checkbox-outline">
                                        Авария
                                        {!! Form::checkbox( 'emergency', 1, \Input::old( 'emergency', $type->emergency ) ) !!}
                                        <span></span>
                                    </label>
                                    <label class="mt-checkbox mt-checkbox-outline">
                                        Платно
                                        {!! Form::checkbox( 'is_pay', 1, \Input::old( 'is_pay', $type->is_pay ) ) !!}
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group hidden-print">
                            <div class="col-xs-12">
                                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                            </div>
                        </div>

                        {!! Form::close() !!}

                    </div>
                </div>
            </div>

        </div>

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection