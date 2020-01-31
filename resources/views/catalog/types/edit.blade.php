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

        {!! Form::model( $type, [ 'method' => 'put', 'route' => [ 'types.update', $type->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="panel panel-default">
            <div class="panel-body">

                <div class="form-group">
                    <div class="col-lg-8">
                        {!! Form::label( 'parent_id', 'Родитель', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'parent_id', $parents, \Input::old( 'parent_id', $type->parent_id ), [ 'class' => 'form-control select2', 'placeholder' => 'Родитель', 'data-placeholder' => 'Родитель' ] ) !!}
                    </div>
                    <div class="col-lg-4">
                        {!! Form::label( 'group_id', 'Группа', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'group_id', $groups, \Input::old( 'group_id', $type->group_id ), [ 'class' => 'form-control select2', 'placeholder' => 'Группа', 'data-placeholder' => 'Группа' ] ) !!}
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-lg-12">
                        {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'name', \Input::old( 'name', $type->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование', 'required' ] ) !!}
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
                        @if ( ! $type->parent )
                            <a href="{{ route( 'types.index', [ 'parent_id' => $type->id ] ) }}"
                               class="btn btn-default btn-circle">
                                Состав
                                <span class="badge">
                                    {{ $type->childs()->count() }}
                                </span>
                            </a>
                        @endif
                        <a href="{{ route( 'types.managements', $type->id ) }}" class="btn btn-default btn-circle">
                            УО
                            <span class="badge">
                                {{ $type->managements()->count() }}
                            </span>
                        </a>
                    </div>
                </div>

            </div>

        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Сроки и сезонность</h3>
            </div>
            <div class="panel-body">

                <div class="form-group">

                    <div class="col-lg-3">
                        {!! Form::label( 'period_acceptance', 'Период на принятие заявки в работу, час', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::number( 'period_acceptance', \Input::old( 'period_acceptance', $type->period_acceptance ), [ 'class' => 'form-control', 'placeholder' => 'Период на принятие заявки в работу, час', 'step' => 0.1, 'min' => 0 ] ) !!}
                    </div>

                    <div class="col-lg-3">
                        {!! Form::label( 'period_execution', 'Период на исполнение, час', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::number( 'period_execution', \Input::old( 'period_execution', $type->period_execution ), [ 'class' => 'form-control', 'placeholder' => 'Период на исполнение, час', 'step' => 0.1, 'min' => 0 ] ) !!}
                    </div>

                    <div class="col-lg-6">
                        {!! Form::label( 'season', 'Сезонность устранения', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'season', \Input::old( 'season', $type->season ), [ 'class' => 'form-control', 'placeholder' => 'Сезонность устранения' ] ) !!}
                    </div>

                </div>

                <div class="form-group hidden-print">
                    <div class="col-xs-12">
                        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                </div>

            </div>

        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">АИС ГЖИ</h3>
                    </div>
                    <div class="panel-body">

                        <div class="form-group">

                            <div class="col-md-12 margin-bottom-15">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        Mosreg ID
                                    </span>
                                    {!! Form::text( 'mosreg_id', \Input::old( 'mosreg_id', $type->mosreg_id ), [ 'class' => 'form-control', 'placeholder' => 'Mosreg ID' ] ) !!}
                                </div>
                            </div>

                        </div>

                        <div class="form-group hidden-print">
                            <div class="col-xs-12">
                                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                            </div>
                        </div>

                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="form-group">
                            <div class="col-md-12 margin-bottom-15">
                            {!! Form::label( 'provider_id', 'Провайдер', [ 'class' => 'control-label' ] ) !!}
                            {!! Form::select( 'provider_id', \App\Models\Provider::pluck('name', 'id')->toArray(), \Input::old( 'provider_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Провайдер' ] ) !!}
                            </div>
                        </div>
                        <div class="form-group hidden-print">
                            <div class="col-xs-12">
                                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Настройки</h3>
                    </div>
                    <div class="panel-body">

                        <div class="form-group">
                            <div class="col-lg-4">
                                <div class="input-group margin-bottom-15">
                                    <span class="input-group-addon">
                                        Цвет
                                    </span>
                                    {!! Form::color( 'color', \Input::old( 'color', $type->color ), [ 'class' => 'form-control', 'placeholder' => 'Цвет' ] ) !!}
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="mt-checkbox-list">
                                    <label class="mt-checkbox mt-checkbox-outline">
                                        Требуется акт
                                        {!! Form::checkbox( 'need_act', 1, \Input::old( 'need_act', $type->need_act ) ) !!}
                                        <span></span>
                                    </label>
                                    <label class="mt-checkbox mt-checkbox-outline">
                                        Аварийная
                                        {!! Form::checkbox( 'emergency', 1, \Input::old( 'emergency', $type->emergency ) ) !!}
                                        <span></span>
                                    </label>
                                    <label class="mt-checkbox mt-checkbox-outline">
                                        Платно
                                        {!! Form::checkbox( 'is_pay', 1, \Input::old( 'is_pay', $type->is_pay ) ) !!}
                                        <span></span>
                                    </label>
                                    <label class="mt-checkbox mt-checkbox-outline">
                                        Отображать в Отключениях
                                        {!! Form::checkbox( 'works', 1, \Input::old( 'works', $type->works ) ) !!}
                                        <span></span>
                                    </label>
                                    <label class="mt-checkbox mt-checkbox-outline">
                                        Отображать в ЛК
                                        {!! Form::checkbox( 'lk', 1, \Input::old( 'lk', $type->lk ) ) !!}
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

                    </div>
                </div>
            </div>

        </div>

        {!! Form::close() !!}

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection
