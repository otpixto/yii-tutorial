@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Администрирование' ],
        [ 'Роли', route( 'roles.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->admin || \Auth::user()->can( 'admin.roles.create' ) )

        {!! Form::open( [ 'url' => route( 'roles.store' ), 'class' => 'form-horizontal submit-loading' ] ) !!}

        <div class="form-group">
            {!! Form::label( 'code', 'Код', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'code', \Input::old( 'code' ), [ 'class' => 'form-control', 'placeholder' => 'Код' ] ) !!}
        </div>

        <div class="form-group">
            {!! Form::label( 'name', 'Наименование', [ 'class' => 'control-label' ] ) !!}
            {!! Form::text( 'name', \Input::old( 'name' ), [ 'class' => 'form-control', 'placeholder' => 'Наименование' ] ) !!}
        </div>

        <div class="form-group">
            {!! Form::label( 'guard', 'Guard', [ 'class' => 'control-label' ] ) !!}
            {!! Form::select( 'guard', $guards, \Input::old( 'guard', config( 'defaults.guard' ) ), [ 'class' => 'form-control' ] ) !!}
        </div>

        <div class="caption caption-md">
            <i class="icon-globe theme-font hide"></i>
            <span class="caption-subject font-blue-madison bold uppercase">Права доступа</span>
        </div>

        @if ( $perms_tree )
            <div id="tree" class="tree-demo jstree jstree-2 jstree-default jstree-checkbox-selection" role="tree" aria-multiselectable="true" tabindex="0" aria-busy="false" aria-selected="false">
                <ul class="jstree-container-ul jstree-children jstree-wholerow-ul jstree-no-dots" role="group">
                    @include( 'admin.perms.tree', [ 'tree' => $perms_tree ] )
                </ul>
            </div>
        @endif

        <div id="perms-results"></div>

        <div class="margin-top-10">
            {!! Form::submit( 'Создать', [ 'class' => 'btn green' ] ) !!}
        </div>

        {!! Form::close() !!}

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/jstree/dist/themes/default/style.min.css" rel="stylesheet" type="text/css" />
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/jstree/dist/jstree.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                @if ( $perms_tree )

                    $( '#tree' )

                    .on( 'changed.jstree', function ( e, data )
                    {
                        $( '#perms-results' ).empty();
                        $.each( data.selected, function ( i, code )
                        {
                            $( '#perms-results' ).append(
                                $( '<input type="hidden" name="perms[]">' ).val( code )
                            );
                        });
                    })

                    .jstree(
                        {
                            'plugins': [
                                'wholerow',
                                'checkbox'
                            ],
                            "core": {
                                "themes":
                                    {
                                        "icons":false
                                    }
                            }
                        });

                @endif

            });

    </script>
@endsection