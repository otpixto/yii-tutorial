@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Управляющие организации', route( 'managements.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.managements.edit' ) )

        {!! Form::open( [ 'url' => route( 'managements.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">

            <div class="col-xs-3">
                {!! Form::label( 'provider_id', 'Поставщик', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'provider_id', $providers->pluck( 'name', 'id' ), \Input::old( 'provider_id' ), [ 'class' => 'form-control select2', 'data-placeholder' => 'Поставщик' ] ) !!}
            </div>

            <div class="col-xs-9">
                {!! Form::label( 'address_id', 'Адрес', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'address_id', [], \Input::old( 'address_id' ), [ 'class' => 'form-control select2-ajax', 'placeholder' => 'Адрес офиса', 'data-ajax--url' => route( 'buildings.search' ), 'data-placeholder' => 'Адрес офиса' ] ) !!}
            </div>

        </div>

        <div class="form-group">

            <div class="col-xs-4">
                {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
            </div>

            <div class="col-xs-4">
                {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'phone', \Input::old( 'phone' ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
            </div>

            <div class="col-xs-4">
                {!! Form::label( 'phone2', 'Доп. телефон', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'phone2', \Input::old( 'phone2' ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Доп. телефон' ] ) !!}
            </div>

        </div>

        <div class="form-group">

            <div class="col-xs-4">
                {!! Form::label( 'director', 'ФИО руководителя', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'director', \Input::old( 'director' ), [ 'class' => 'form-control', 'placeholder' => 'ФИО руководителя' ] ) !!}
            </div>

            <div class="col-xs-4">
                {!! Form::label( 'email', 'E-mail', [ 'class' => 'control-label' ] ) !!}
                {!! Form::email( 'email', \Input::old( 'email' ), [ 'class' => 'form-control', 'placeholder' => 'E-mail' ] ) !!}
            </div>

            <div class="col-xs-4">
                {!! Form::label( 'site', 'Сайт', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'site', \Input::old( 'site' ), [ 'class' => 'form-control', 'placeholder' => 'Сайт' ] ) !!}
            </div>

        </div>

        <div class="form-group">

            <div class="col-xs-4">
                {!! Form::label( 'category_id', 'Категория ЭО', [ 'class' => 'control-label' ] ) !!}
                {!! Form::select( 'category_id', [ null => ' -- выберите из списка -- ' ] + \App\Models\Management::$categories, \Input::old( 'category_id' ), [ 'class' => 'form-control select2', 'placeholder' => 'Категория ЭО' ] ) !!}
            </div>

            <div class="col-xs-4">
                {!! Form::label( 'services', 'Услуги', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'services', \Input::old( 'services' ), [ 'class' => 'form-control', 'placeholder' => 'Услуги' ] ) !!}
            </div>

            <div class="col-xs-4">
                {!! Form::label( 'schedule', 'График работы', [ 'class' => 'control-label' ] ) !!}
                {!! Form::text( 'schedule', \Input::old( 'schedule' ), [ 'class' => 'form-control', 'placeholder' => 'График работы' ] ) !!}
            </div>

        </div>

        <div class="form-group">
            <div class="col-xs-12">
                {!! Form::submit( 'Добавить', [ 'class' => 'btn green' ] ) !!}
            </div>
        </div>

        {!! Form::close() !!}

    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection

@section( 'js' )
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )
            .ready(function()
            {

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

            });

    </script>
@endsection