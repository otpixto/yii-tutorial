@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Права' ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="row margin-bottom-15">
        <div class="col-xs-12">
            <a href="{{ route( 'perms.create' ) }}" class="btn btn-success">
                <i class="fa fa-plus"></i>
                Создать права
            </a>
        </div>
    </div>

    <div class="todo-ui">
        <div class="todo-sidebar">
            <div class="portlet light ">
                <div class="portlet-title">
                    <div class="caption">
                        <span class="caption-subject font-green-sharp bold uppercase">ПОИСК</span>
                    </div>
                </div>
                <div class="portlet-body todo-project-list-content" style="height: auto;">
                    <div class="todo-project-list">
                        {!! Form::open( [ 'method' => 'get' ] ) !!}
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

                    @if ( $perms_tree )
                        <div id="tree" class="tree-demo jstree jstree-2 jstree-default jstree-checkbox-selection" role="tree" aria-multiselectable="true" tabindex="0" aria-activedescendant="j2_1" aria-busy="false" aria-selected="false">
                            <ul class="jstree-container-ul jstree-children jstree-wholerow-ul jstree-no-dots" role="group">
                                @include( 'admin.perms.tree', [ 'tree' => $perms_tree ] )
                            </ul>
                        </div>
                    @else

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
                                    <th>
                                        Guard
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
                                    <td>
                                        {{ $perm->guard_name }}
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route( 'perms.edit', $perm->id ) }}" class="btn btn-xs btn-info">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        {{ $perms->render() }}

                    @endif

                </div>
            </div>
        </div>
        <!-- END TODO CONTENT -->
    </div>

@endsection

@section( 'css' )
    <link href="/assets/apps/css/todo-2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/jstree/dist/themes/default/style.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/jstree/dist/jstree.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                @if ( $perms_tree )

                    $('#tree').jstree({
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