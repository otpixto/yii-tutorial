@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Поставщики', route( 'providers.index' ) ],
        [ $providerKey->provider->name, route( 'providers.edit', $providerKey->provider->id ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin )

        {!! Form::open( [ 'method' => 'post', 'url' => route( 'providers.keys.update', [ $provider->id, $providerKey->id ] ), 'class' => 'form-horizontal submit-loading' ] ) !!}
        <div class="form-group">
            {!! Form::label( 'api_key', 'Ключ', [ 'class' => 'control-label col-md-4' ] ) !!}
            <div class="col-md-8">
                {!! Form::text( 'api_key', $providerKey->api_key, [ 'class' => 'form-control', 'readonly' ] ) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label( 'token_life', 'Время жизни токена (минут)', [ 'class' => 'control-label col-md-4' ] ) !!}
            <div class="col-md-8">
                {!! Form::number( 'token_life', $providerKey->token_life, [ 'class' => 'form-control', 'step' => 1, 'min' => 1 ] ) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label( 'description', 'Описание', [ 'class' => 'control-label col-md-4' ] ) !!}
            <div class="col-md-8">
                {!! Form::textarea( 'description', $providerKey->description, [ 'class' => 'form-control', 'placeholder' => 'Описание' ] ) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label( 'ip', 'IP', [ 'class' => 'control-label col-md-4' ] ) !!}
            <div class="col-md-8">
                {!! Form::textarea( 'ip', $providerKey->ip, [ 'class' => 'form-control', 'placeholder' => 'IP' ] ) !!}
            </div>
        </div>
		<div class="form-group">
            {!! Form::label( 'referer', 'Referer', [ 'class' => 'control-label col-md-4' ] ) !!}
            <div class="col-md-8">
                {!! Form::textarea( 'referer', $providerKey->referer, [ 'class' => 'form-control', 'placeholder' => 'Referer' ] ) !!}
            </div>
        </div>
        <div class="form-group hidden-print">
            <div class="col-md-8 col-md-offset-4">
                {!! Form::submit( 'Сохранить', [ 'class' => 'btn green' ] ) !!}
            </div>
        </div>
        {!! Form::close() !!}

        <div class="panel panel-default" id="tokens">
            <div class="panel-heading">
                <h3 class="panel-title">Активные токены</h3>
            </div>
            <div class="panel-body">

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>
                                Пользователь
                            </th>
                            <th>
                                HTTP USER AGENT
                            </th>
                            <th>
                                IP
                            </th>
                            <th>
                                Последняя активность
                            </th>
                            <th>

                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ( $providerKey->providerTokens as $providerToken )
                        <tr>
                            <td>
                                <a href="{{ route( 'users.edit', $providerToken->user->id ) }}">
                                    {{ $providerToken->user->getName() }}
                                </a>
                            </td>
                            <td>
                                {{ $providerToken->http_user_agent }}
                            </td>
                            <td>
                                {{ $providerToken->ip }}
                            </td>
                            <td>
                                {{ $providerToken->updated_at->format( 'd.m.Y H:i:s' ) }}
                            </td>
                            <td class="text-right">
                                <button type="button" class="btn btn-xs btn-danger" data-delete="provider-token" data-id="{{ $providerToken->id }}">
                                    <i class="fa fa-remove"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>

        </div>

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'js' )
    <script type="text/javascript">

        $( document )

            .on( 'click', '[data-delete="provider-token"]', function ( e )
            {

                e.preventDefault();

                var token_id = $( this ).attr( 'data-id' );
                var obj = $( this ).closest( 'tr' );

                bootbox.confirm({
                    message: 'Удалить токен?',
                    size: 'small',
                    buttons: {
                        confirm: {
                            label: '<i class="fa fa-check"></i> Да',
                            className: 'btn-success'
                        },
                        cancel: {
                            label: '<i class="fa fa-times"></i> Нет',
                            className: 'btn-danger'
                        }
                    },
                    callback: function ( result )
                    {
                        if ( result )
                        {

                            obj.hide();

                            $.ajax({
                                url: '{{ route( 'providers.tokens.del', $providerKey->id ) }}',
                                method: 'delete',
                                data: {
                                    token_id: token_id
                                },
                                success: function ()
                                {
                                    obj.remove();
                                },
                                error: function ( e )
                                {
                                    obj.show();
                                    alert( e.statusText );
                                }
                            });

                        }
                    }
                });

            });

    </script>
@endsection