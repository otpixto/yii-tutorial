@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'method' => 'get', 'class' => 'submit-loading hidden-print margin-bottom-15', 'id' => 'report-form' ] ) !!}

    <div class="row margin-bottom-15">
        <div class="col-md-6 col-md-offset-3">
            {!! Form::label( 'managements_ids', 'УО', [ 'class' => 'control-label' ] ) !!}
            <select class="mt-multiselect form-control" multiple="multiple" data-label="left" id="managements_ids" name="managements_ids[]">
                @foreach ( $availableManagements as $management => $arr )
                    <optgroup label="{{ $management }}">
                        @foreach ( $arr as $management_id => $management_name )
                            <option value="{{ $management_id }}" @if ( in_array( $management_id, $managements_ids ) ) selected="selected" @endif>
                                {{ $management_name }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row margin-bottom-15">
        <div class="col-md-6 col-md-offset-3">
            {!! Form::label( 'date_from', 'Период', [ 'class' => 'control-label' ] ) !!}
            <div class="input-group">
                <span class="input-group-addon">
                    от
                </span>
                <input class="form-control" name="date_from" type="datetime-local" value="{{ $date_from->format( 'Y-m-d\TH:i' ) }}" id="date_from" max="{{ \Carbon\Carbon::now()->format( 'Y-m-d\TH:i' ) }}" />
                <span class="input-group-addon">
                    до
                </span>
                <input class="form-control" name="date_to" type="datetime-local" value="{{ $date_to->format( 'Y-m-d\TH:i' ) }}" id="date_to" max="{{ \Carbon\Carbon::now()->format( 'Y-m-d\TH:i' ) }}" />
            </div>
        </div>
    </div>

    <div class="row margin-bottom-15">
        <div class="col-xs-offset-3 col-xs-3">
            {!! Form::submit( 'Показать', [ 'class' => 'btn btn-primary' ] ) !!}
            @if ( count( $data ) && \Auth::user()->can( 'reports.export' ) )
                <a href="{{ Request::fullUrl() }}&export=1" class="btn btn-default">
                    <i class="fa fa-download"></i>
                    Выгрузить в Excel
                </a>
            @endif
        </div>
    </div>

    {!! Form::close() !!}

    @if ( count( $data ) )

        <div class="visible-print title">
            {{ $title }}
        </div>

        @foreach ( $data as $management_name => $row )
            <div class="row margin-bottom-15">
                <div class="col-lg-2 text-lg-right bold">
                    {{ $management_name }}
                </div>
                <div class="col-lg-10">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>
                                    &nbsp;
                                </th>
                                <th class="text-center">
                                    Выполнено
                                </th>
                                <th class="text-center">
                                    В работе
                                </th>
                                <th class="text-center">
                                    Отложено
                                </th>
                                <th class="text-center">
                                    Итого
                                </th>
                                <th class="text-center">
                                    Процент выполнения
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ( $row[ 'groups' ] as $group_name => $count )
                            <tr>
                                <th>
                                    {{ $group_name }}
                                </th>
                                <td class="text-center">
                                    {{ $count[ 'completed' ] }}
                                </td>
                                <td class="text-center">
                                    {{ $count[ 'in_process' ] }}
                                </td>
                                <td class="text-center">
                                    {{ $count[ 'waiting' ] }}
                                </td>
                                <td class="text-center">
                                    {{ $count[ 'total' ] }}
                                </td>
                                <td class="text-center">
                                    {{ $count[ 'completed_percent' ] }}
                                </td>
                            </tr>
                        @endforeach
                            <tr>
                                <th class="text-right">
                                    Всего
                                </th>
                                <th class="text-center">
                                    {{ $row[ 'totals' ][ 'completed' ] }}
                                </th>
                                <th class="text-center">
                                    {{ $row[ 'totals' ][ 'in_process' ] }}
                                </th>
                                <th class="text-center">
                                    {{ $row[ 'totals' ][ 'waiting' ] }}
                                </th>
                                <th class="text-center">
                                    {{ $row[ 'totals' ][ 'total' ] }}
                                </th>
                                <th class="text-center">
                                    {{ $row[ 'totals' ][ 'completed_percent' ] }}
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" />
    <style>
        @media print {
            td, th {
                font-size: 85% !important;
            }
            .breadcrumbs {
                display: none;
            }
            .title {
                font-weight: bold;
                margin: 10px 0;
            }
        }
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js" type="text/javascript"></script>
    <script type="text/javascript">

        $( document )

            .ready( function ()
            {

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

            });

    </script>
@endsection