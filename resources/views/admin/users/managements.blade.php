@extends( 'admin.users.template' )

@section( 'users.content' )

    @if ( \Auth::user()->can( 'admin.users.managements' ) )
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-plus"></i>
                    Добавить УО
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::model( $user, [ 'method' => 'put', 'route' => [ 'users.managements.add', $user->id ], 'class' => 'submit-loading' ] ) !!}
                <div class="row">
                    <div class="col-md-12">
                        <select class="mt-multiselect form-control" multiple="multiple" data-label="left"
                                id="managements" name="managements[]">
                            @foreach ( $availableManagements as $management => $arr )
                                <optgroup label="{{ $management }}">
                                    @foreach ( $arr as $management_id => $management_name )
                                        <option value="{{ $management_id }}">
                                            {{ $management_name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row margin-top-15">
                    <div class="col-md-12">
                        {!! Form::submit( 'Добавить', [ 'class' => 'btn btn-success' ] ) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    @endif

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-search"></i>
                Поиск
            </h3>
        </div>
        <div class="panel-body">
            {!! Form::open( [ 'method' => 'get', 'route' => [ 'users.managements', $user->id ], 'class' => 'form-horizontal submit-loading' ] ) !!}
            <div class="form-group">
                <div class="col-md-12">
                    {!! Form::text( 'search', $search, [ 'class' => 'form-control' ] ) !!}
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    {!! Form::submit( 'Найти', [ 'class' => 'btn btn-success' ] ) !!}
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">

            {{ $userManagements->render() }}

            @forelse ( $userManagements as $r )
                <div class="margin-bottom-5">
                    @if ( \Auth::user()->can( 'admin.users.managements' ) )
                        <button type="button" class="btn btn-xs btn-danger" data-delete="user-management"
                                data-management="{{ $r->id }}">
                            <i class="fa fa-remove"></i>
                        </button>
                    @endif
                    <a href="{{ route( 'managements.edit', $r->id ) }}">
                        @if ( $r->parent )
                            <span class="text-muted">
                                {{ $r->parent->name }}
                            </span>
                        @endif
                        {{ $r->name }}
                    </a>
                </div>
            @empty
                @include( 'parts.error', [ 'error' => 'Ничего не назначено' ] )
            @endforelse

            {{ $userManagements->render() }}

            <div class="row">
                <div class="col-md-2 center-align">
                    {!! Form::model( $user, [ 'method' => 'delete', 'route' => [ 'users.managements.empty', $user->id ], 'class' => 'form-horizontal submit-loading', 'data-confirm' => 'Вы уверены?' ] ) !!}
                    <div class="form-group margin-top-15">
                        <div class="col-md-12">
                            {!! Form::submit( 'Удалить все', [ 'class' => 'btn btn-danger' ] ) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>

                <div class="col-md-4 center-align">
                    <div class="form-group margin-top-15">
                        <button class="btn btn-info" id="alOtherUser">
                            Привязать ВСЕ к другому пользователю
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet"
          type="text/css"/>
@endsection

@section( 'js' )
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <script src="/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js"
            type="text/javascript"></script>
    <script type="text/javascript">

        $(document)

            .ready(function () {

                $('.mt-multiselect').multiselect({
                    disableIfEmpty: true,
                    enableFiltering: true,
                    includeSelectAllOption: true,
                    enableCaseInsensitiveFiltering: true,
                    enableClickableOptGroups: true,
                    buttonWidth: '100%',
                    maxHeight: '300',
                    buttonClass: 'mt-multiselect btn btn-default',
                    numberDisplayed: 5,
                    nonSelectedText: '-',
                    nSelectedText: ' выбрано',
                    allSelectedText: 'Все',
                    selectAllText: 'Выбрать все',
                    selectAllValue: ''
                });

            })

            .on('click', '[data-delete="user-management"]', function (e) {

                e.preventDefault();

                var management_id = $(this).attr('data-management');
                var obj = $(this).closest('div');

                bootbox.confirm({
                    message: 'Удалить привязку?',
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
                    callback: function (result) {
                        if (result) {

                            obj.hide();

                            $.ajax({
                                url: '{{ route( 'users.managements.del', $user->id ) }}',
                                method: 'delete',
                                data: {
                                    management_id: management_id
                                },
                                success: function () {
                                    obj.remove();
                                },
                                error: function (e) {
                                    obj.show();
                                    alert(e.statusText);
                                }
                            });

                        }
                    }
                })


            })
            .on('click', '#alOtherUser', function () {
                Swal.fire({
                    title: '',
                    icon: 'info',
                    html: '<h6><b>Вы уверены?</b></h6>',
                    showCloseButton: true,
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText:
                        '<h6><b>ОК</b></h6>',
                    confirmButtonAriaLabel: 'ОК',
                    cancelButtonText: '<h6><b>Отмена</b></h6>',
                    cancelButtonAriaLabel: 'Thumbs down'
                }).then((result) => {

                    if (result.value) {
                        window.location.href = '{{ route('users.managements.massManagementsEdit', [ 'user_id' => $user->id ]) }}';

                        return false;
                    }

                })
            })

    </script>
@endsection