@extends( 'admin.users.template' )

@section( 'users.content' )

    <div class="portlet light">
        <div class="portlet-title tabbable-line">
            <div class="caption">
                <i class="fa fa-user"></i>
                @if ( $user->email )
                    <span class="caption-subject bold">
                        {{ $user->email }}
                    </span>
                    <span class="caption-helper">логин для входа</span>
                @else
                    <span class="caption-subject bold">
                        Данные пользователя
                    </span>
                @endif
            </div>
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#userinfo" data-toggle="tab" aria-expanded="true">
                        Данные пользователя
                    </a>
                </li>
                <li>
                    <a href="#password" data-toggle="tab" aria-expanded="false">
                        Сменить пароль
                    </a>
                </li>
                <li>
                    <a href="#photo" data-toggle="tab" aria-expanded="false">
                        Фотография
                    </a>
                </li>
            </ul>
        </div>
        <div class="portlet-body">
            <div class="tab-content">
                <div class="tab-pane active" id="userinfo">
                    {!! Form::model( $user, [ 'method' => 'put', 'route' => [ 'users.update', $user->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                    <div class="form-group">
                        {!! Form::label( 'active', 'Активен', [ 'class' => 'control-label col-md-3' ] ) !!}
                        <div class="col-md-6">
                            {!! Form::checkbox( 'active', 1, $user->active, [ 'class' => 'make-switch', 'data-on-color' => 'success', 'data-off-color' => 'danger' ] ) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label( 'lastname', 'Фамилия', [ 'class' => 'control-label col-md-3' ] ) !!}
                        <div class="col-md-6">
                            {!! Form::text( 'lastname', \Input::old( 'lastname', $user->lastname ), [ 'class' => 'form-control', 'placeholder' => 'Фамилия' ] ) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label( 'firstname', 'Имя', [ 'class' => 'control-label col-md-3' ] ) !!}
                        <div class="col-md-6">
                            {!! Form::text( 'firstname', \Input::old( 'firstname', $user->firstname ), [ 'class' => 'form-control', 'placeholder' => 'Имя' ] ) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label( 'middlename', 'Отчество', [ 'class' => 'control-label col-md-3' ] ) !!}
                        <div class="col-md-6">
                            {!! Form::text( 'middlename', \Input::old( 'middlename', $user->middlename ), [ 'class' => 'form-control', 'placeholder' => 'Отчество' ] ) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label( 'phone', 'Телефон', [ 'class' => 'control-label col-md-3' ] ) !!}
                        <div class="col-md-6">
                            {!! Form::text( 'phone', \Input::old( 'phone', $user->phone ), [ 'class' => 'form-control mask_phone', 'placeholder' => 'Телефон' ] ) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label( 'prefix', 'Приставка', [ 'class' => 'control-label col-md-3' ] ) !!}
                        <div class="col-md-6">
                            {!! Form::text( 'prefix', \Input::old( 'prefix', $user->prefix ), [ 'class' => 'form-control', 'placeholder' => 'Приставка' ] ) !!}
                        </div>
                    </div>
					<div class="form-group">
                        {!! Form::label( 'tabs_limit', 'Лимит вкладок', [ 'class' => 'control-label col-md-3' ] ) !!}
                        <div class="col-md-6">
                            {!! Form::number( 'tabs_limit', \Input::old( 'tabs_limit', $user->tabs_limit ), [ 'class' => 'form-control', 'placeholder' => 'Лимит вкладок' ] ) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
                <div class="tab-pane" id="password">
                    {!! Form::model( $user, [ 'method' => 'put', 'route' => [ 'users.change_password', $user->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
                        <div class="form-group">
                            {!! Form::label( 'password', 'Пароль', [ 'class' => 'control-label col-md-3' ] ) !!}
                            <div class="col-md-6">
                                {!! Form::password( 'password', [ 'class' => 'form-control', 'placeholder' => 'Пароль', 'minlength' => 6, 'required' ] ) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label( 'password_confirmation', 'Повторите пароль', [ 'class' => 'control-label col-md-3' ] ) !!}
                            <div class="col-md-6">
                                {!! Form::password( 'password_confirmation', [ 'class' => 'form-control', 'placeholder' => 'Повторите пароль', 'minlength' => 6, 'required' ] ) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-3">
                                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
                <div class="tab-pane" id="photo">
                    {!! Form::model( $user, [ 'method' => 'put', 'route' => [ 'users.upload_photo', $user->id ], 'files' => true ] ) !!}
                        <div class="row">
                            <div class="col-lg-4 col-md-6 text-right text-sm-center text-xs-center">
                                <label for="image" class="img-thumbnail">
                                    <img src="/images/noimage.png" alt="" />
                                </label>
                            </div>
                            <div class="col-lg-5 col-md-6">
                                <div class="form-group">
                                    {!! Form::file( 'image', [ 'class' => 'form-control', 'placeholder' => 'Выберите изображение', 'id' => 'image', 'accept' => 'image/*' ] ) !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
                                </div>
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="portlet light">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-link"></i>
                        <span class="caption-subject bold">
                            УО
                        </span>
                    </div>
                    <div class="actions">
                        <a href="{{ route( 'users.managements', $user->id ) }}" class="btn btn-circle btn-default">
                            <i class="fa fa-pencil"></i>
                            Редактировать
                        </a>
                    </div>
                </div>
                <div class="portlet-body">
                    <ol>
                    @forelse ( $user->managements as $management )
                        <li>
                            <a href="{{ route( 'managements.edit', $management->id ) }}">
                                {{ $management->name }}
                            </a>
                        </li>
                    @empty
                        @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                    @endforelse
                    </ol>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="portlet light">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-link"></i>
                        <span class="caption-subject bold">
                            Поставщики
                        </span>
                    </div>
                    <div class="actions">
                        <a href="{{ route( 'users.providers', $user->id ) }}" class="btn btn-circle btn-default">
                            <i class="fa fa-pencil"></i>
                            Редактировать
                        </a>
                    </div>
                </div>
                <div class="portlet-body">
                    <ol>
                        @forelse ( $user->providers as $provider )
                            <li>
                                <a href="{{ route( 'providers.edit', $provider->id ) }}">
                                    {{ $provider->name }}
                                </a>
                            </li>
                        @empty
                            @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                        @endforelse
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if ( \Auth::user()->can( 'admin.users.types' ) )
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-link"></i>
                            <span class="caption-subject bold">
                                Классификатор
                            </span>
                        </div>
                        <div class="actions">
                            <a href="{{ route( 'users.types', $user->id ) }}" class="btn btn-circle btn-default">
                                <i class="fa fa-pencil"></i>
                                Редактировать
                            </a>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <ol>
                            @forelse ( $user->types as $type )
                                <li>
                                    <a href="{{ route( 'types.edit', $type->id ) }}">
                                        {{ $type->name }}
                                    </a>
                                </li>
                            @empty
                                @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                            @endforelse
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@section( 'users.css' )
    <style>
        ol {
            margin: 0 15px;
            padding: 0;
        }
    </style>
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