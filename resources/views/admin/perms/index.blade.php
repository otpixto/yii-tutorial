@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.perms.create' ) )
        <div class="row margin-bottom-15">
            <div class="col-xs-12">
                <a href="{{ route( 'perms.create' ) }}" class="btn btn-success btn-lg">
                    <i class="fa fa-plus"></i>
                    Создать права
                </a>
            </div>
        </div>
    @endif

    <div class="todo-ui">
        <div class="todo-sidebar">
            <div class="portlet light ">
                <div class="portlet-title">
                    <div class="caption">
                        <span class="caption-subject font-green-sharp bold uppercase">ПОИСК</span>
                    </div>
                    <a href="{{ route( 'perms.index' ) }}" class="btn btn-danger pull-right">сбросить</a>
                </div>
                <div class="portlet-body todo-project-list-content" style="height: auto;">
                    <div class="todo-project-list">
                        {!! Form::open( [ 'method' => 'get' ] ) !!}
                        {!! Form::hidden( 'guard', $guard ) !!}
                        <div class="row">
                            <div class="col-xs-12">
                                {!! Form::text( 'search', $search ?? null, [ 'class' => 'form-control' ] ) !!}
                            </div>
                        </div>
                        <div class="row margin-top-10">
                            <div class="col-xs-12">
                                {!! Form::submit( 'Найти', [ 'class' => 'btn btn-info btn-block' ] ) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
        <!-- END TODO SIDEBAR -->

        <!-- BEGIN TODO CONTENT -->
        <div class="todo-content">
            <div class="portlet light ">
                <div class="portlet-body">

                    <ul class="nav nav-tabs">
                        @foreach ( $guards as $_guard )
                            <li role="presentation" @if ( $_guard == $guard ) class="active" @endif>
                                <a href="?guard={{ $_guard }}">
                                    {{ $_guard }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    @if ( $perms_tree )
                        <div id="perms_tree" class="jstree jstree-2 jstree-default jstree-checkbox-selection" role="tree" aria-multiselectable="true" tabindex="0" aria-activedescendant="j2_1" aria-busy="false" aria-selected="false">
                            @include( 'admin.perms.tree', [ 'perms_tree' => $perms_tree ] )
                        </div>
                    @elseif ( $perms->count() )

                        {{ $perms->render() }}

                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>
                                        Наименование
                                    </th>
                                    <th>
                                        Код
                                    </th>
                                    <th class="text-right">
                                        &nbsp;
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ( $perms as $perm )
                                <tr>
                                    <td>
                                        {{ $perm->name }}
                                    </td>
                                    <td>
                                        {{ $perm->code }}
                                    </td>
                                    <td class="text-right">
                                        @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.perms.edit' ) )
                                            <a href="{{ route( 'perms.edit', $perm->id ) }}" class="btn btn-info">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        {{ $perms->render() }}

                    @else
                        @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                    @endif

                </div>
            </div>
        </div>
        <!-- END TODO CONTENT -->
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/jstree/dist/themes/default/style.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/jstree/dist/jstree.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                @if ( $perms_tree )

                    $( '#perms_tree' ).jstree({
                        'plugins': [],
                        "core": {
                            "themes":{
                                "icons":false
                            }
                        }
                    })
                    .bind('select_node.jstree', function( e, data )
                    {
                        window.location.href = data.node.a_attr.href;
                    });

                @endif

            });

    </script>
@endsection