@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', route( 'home' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->canOne( 'works.show', 'works.all' ) )

        @if( \Auth::user()->canOne( 'works.create', 'works.export' ) )
            <div class="row margin-bottom-15 hidden-print">
                <div class="col-xs-6">
                    @can( 'works.create' )
                        <a href="{{ route( 'works.create' ) }}" class="btn btn-success btn-lg">
                            <i class="fa fa-plus"></i>
                            Добавить сообщение
                        </a>
                    @endcan
                </div>
                <div class="col-xs-6 text-right">
                    @can( 'works.export' )
                        <a href="?export=1&{{ Request::getQueryString() }}" class="btn btn-default btn-lg">
                            <i class="fa fa-download"></i>
                            Выгрузить в Excel
                        </a>
                    @endcan
                </div>
            </div>
        @endcan

        @include( 'works.search' )

        <div class="row margin-top-15">
            <div class="col-xs-12">

                <div class="row">
                    <div class="col-md-8">
                        {{ $works->render() }}
                    </div>
                    <div class="col-md-4 text-right margin-top-10 margin-bottom-10">
                        <span class="label label-info">
                            Найдено: <b>{{ $works->total() }}</b>
                        </span>
                    </div>
                </div>

                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr class="info">
                            <th>
                                Номер сообщения
                            </th>
                            <th>
                                Основание
                            </th>
                            <th>
                                Адрес работ
                            </th>
                            <th>
                                Категория
                            </th>
                            <th>
                                Исполнитель работ
                            </th>
                            <th>
                                Состав работ
                            </th>
                            <th>
                                &nbsp;Дата начала
                            </th>
                            <th colspan="3">
                                &nbsp;Дата окончания (План.|Факт.)
                            </th>
                        </tr>
                    </thead>
                    @if ( $works->count() )
                        <tbody>
                        @foreach ( $works as $work )
                            @include( 'parts.work', [ 'work' => $work ] )
                        @endforeach
                        </tbody>
                    @endif
                </table>

                {{ $works->render() }}

                @if ( ! $works->count() )
                    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
                @endif

            </div>
        </div>

    @else
        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" />
    <style>
        .alert {
            margin-bottom: 0;
        }
        .mt-element-ribbon {

            margin-bottom: 0;
        }
        .mt-element-ribbon .ribbon.ribbon-right {
            top: -8px;
            right: -8px;
        }
        .mt-element-ribbon .ribbon.ribbon-clip {
            left: -18px;
            top: -18px;
        }
        .color-inherit {
            color: inherit;
        }
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-treeview.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

                $( '.datetimepicker' ).datetimepicker({
                    isRTL: App.isRTL(),
                    format: "dd.mm.yyyy hh:ii",
                    autoclose: true,
                    fontAwesome: true,
                    todayBtn: true
                });

                $( '.mt-multiselect' ).multiselect({
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

            .on( 'click', '#segment', function ( e )
            {

                e.preventDefault();

                Modal.create( 'segment-modal', function ()
                {
                    Modal.setTitle( 'Выберите сегмент' );
                    $.get( '{{ route( 'segments.tree' ) }}', function ( response )
                    {
                        var tree = $( '<div></div>' ).attr( 'id', 'segment-tree' );
                        Modal.setBody( tree );
                        tree.treeview({
                            data: response,
                            onNodeSelected: function ( event, node )
                            {
                                $( '#segment_id' ).val( node.id );
                                $( '#segment' ).text( node.text ).removeClass( 'text-muted' );
                            },
                            onNodeUnselected: function ( event, node )
                            {
                                $( '#segment_id' ).val( '' );
                                $( '#segment' ).text( 'Нажмите, чтобы выбрать' ).addClass( 'text-muted' );
                            }
                        });
                    });
                });

            });

    </script>
@endsection