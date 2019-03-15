@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Исполнители', route( 'executors.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.executors.edit' ) )

        <div class="panel panel-default">
            <div class="panel-body">

                {!! Form::model( $executor, [ 'method' => 'put', 'route' => [ 'executors.update', $executor->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}

                <div class="form-group">

                    <div class="col-xs-4">
                        {!! Form::label( 'management_id', 'УО', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'management_id', $availableManagements, \Input::old( 'management_id', $executor->management_id ), [ 'class' => 'form-control select2', 'data-placeholder' => 'УО' ] ) !!}
                    </div>

                    <div class="col-xs-5">
                        {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'name', \Input::old( 'name', $executor->name ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
                    </div>

                    <div class="col-xs-3">
                        {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::text( 'phone', \Input::old( 'phone', $executor->phone ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
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

        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">
                    Привязать пользователя
                </h3>
            </div>
            <div class="panel-body">

                {!! Form::open( [ 'method' => 'post', 'url' => route( 'executors.user', $executor->id ), 'class' => 'form-horizontal submit-loading' ] ) !!}
                {!! Form::hidden( 'provider_id', $executor->management->provider_id ) !!}

                <div class="form-group">

                    <div class="col-md-12">
                        {!! Form::label( 'user_id', 'Пользователь', [ 'class' => 'control-label' ] ) !!}
                        {!! Form::select( 'user_id', $executor->user ? [ $executor->user->id => $executor->user->getName( true ) ] : [], \Input::old( 'user_id', $executor->user_id ), [ 'class' => 'form-control select2-ajax', 'placeholder' => 'Пользователь', 'data-ajax--url' => route( 'users.search' ), 'data-placeholder' => 'Пользователь' ] ) !!}
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

@section( 'js' )
    <script src="/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '.mask_phone' ).inputmask( 'mask', {
                    'mask': '+7 (999) 999-99-99'
                });

            });

    </script>
@endsection