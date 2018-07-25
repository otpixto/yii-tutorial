@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Классификатор', route( 'types.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.types.edit_category' ) )

        <div class="panel panel-default">
            <div class="panel-body">

                {!! Form::model( $category, [ 'method' => 'put', 'route' => [ 'categories.update', $category->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                <div class="form-group">
                    <div class="col-xs-8">
                        {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'name', \Input::old( 'name', $category->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
                    </div>
                    <div class="col-xs-4">
                        {!! Form::label( 'color', 'Цвет', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::color( 'color', \Input::old( 'color', $category->color ), [ 'class' => 'form-control', 'placeholder' => 'Цвет' ] ) !!}
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-xs-6">
                        {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                    </div>
                    <div class="col-xs-6 text-right">
                        <a href="{{ route( 'types.index', [ 'category_id' => $category->id ] ) }}" class="btn btn-default btn-circle">
                            Классификатор
                            <span class="badge">
                                {{ $category->types()->count() }}
                            </span>
                        </a>
                    </div>
                </div>

                {!! Form::close() !!}

            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Настройки</h3>
            </div>
            <div class="panel-body">

                {!! Form::model( $category, [ 'method' => 'put', 'route' => [ 'categories.update', $category->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                {!! Form::hidden( 'name', $category->name ) !!}

                <div class="form-group">
                    <div class="col-xs-12">
                        <div class="mt-checkbox-list">
                            <label class="mt-checkbox mt-checkbox-outline">
                                Требуется акт
                                {!! Form::checkbox( 'need_act', 1, \Input::old( 'need_act', $category->need_act ) ) !!}
                                <span></span>
                            </label>
                            <label class="mt-checkbox mt-checkbox-outline">
                                Авария
                                {!! Form::checkbox( 'emergency', 1, \Input::old( 'emergency', $category->emergency ) ) !!}
                                <span></span>
                            </label>
                            <label class="mt-checkbox mt-checkbox-outline">
                                Платно
                                {!! Form::checkbox( 'is_pay', 1, \Input::old( 'is_pay', $category->is_pay ) ) !!}
                                <span></span>
                            </label>
                            <label class="mt-checkbox mt-checkbox-outline">
                                Отключения
                                {!! Form::checkbox( 'works', 1, \Input::old( 'works', $category->works ) ) !!}
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

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection