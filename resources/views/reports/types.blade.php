@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <p class="visible-print">
        за период с {{ $date_from }} по {{ $date_to }}
    </p>

    {!! Form::open( [ 'method' => 'get', 'class' => 'form-horizontal hidden-print' ] ) !!}
    <div class="form-group">
        {!! Form::label( 'date_from', 'Период', [ 'class' => 'control-label col-xs-3' ] ) !!}
        <div class="col-xs-3">
            {!! Form::text( 'date_from', $date_from, [ 'class' => 'form-control datepicker' ] ) !!}
        </div>
        <div class="col-xs-3">
            {!! Form::text( 'date_to', $date_to, [ 'class' => 'form-control datepicker' ] ) !!}
        </div>
        <div class="col-xs-3">
            {!! Form::submit( 'Применить', [ 'class' => 'btn btn-primary' ] ) !!}
        </div>
    </div>
    {!! Form::close() !!}

    <table class="table table-hover table-striped sortable">
        <thead>
        <tr>
            <th>
                Нименование ЭО
            </th>
            <th>
                Категория проблем
            </th>
            <th class="text-center">
                Всего заявок
            </th>
            <th class="text-center">
                Закрыто
            </th>
            <th>
                &nbsp;
            </th>
            <th class="hidden-print" style="width: 15%;">
                &nbsp;
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach ( $data as $r )
            <tr>
                <td rowspan="{{ count( $r['categories'] ) + 1 }}">
                    {{ $r['name'] }}
                </td>
            </tr>
            @foreach ( $r['categories'] as $category )
                <tr>
                    <td>
                        {{ $category['name'] }}
                    </td>
                    <td class="text-center">
                        {{ $category['total'] }}
                    </td>
                    <td class="text-center">
                        {{ $category['closed'] }}
                    </td>
                    <td class="text-right" data-field="percent">
                        {{ ceil( $category['closed'] * 100 / $category['total'] ) }}%
                    </td>
                    <td class="hidden-print">
                        <div class="progress">
                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ ceil( $category['closed'] * 100 / $category['total'] ) }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ ceil( $category['closed'] * 100 / $category['total'] ) }}%">
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-right">
                    Всего:
                </th>
                <th class="text-center">
                    {{ $summary['total'] }}
                </th>
                <th class="text-center">
                    {{ $summary['closed'] }}
                </th>
                <th class="text-right">
                    {{ ceil( $summary['closed'] * 100 / $summary['total'] ) }}%
                </th>
                <th>
                    <div class="progress">
                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ ceil( $summary['closed'] * 100 / $summary['total'] ) }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ ceil( $summary['closed'] * 100 / $summary['total'] ) }}%">
                        </div>
                    </div>
                </th>
            </tr>
        </tfoot>
    </table>

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <style>
        .progress {
            margin-bottom: 0 !important;
        }
        .table tfoot th, .table tfoot td {
            padding: 8px !important;
        }
    </style>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        $( document )
            .ready(function()
            {
               $( '.datepicker' ).datepicker({
                   format: 'dd.mm.yyyy',
               });
            });
    </script>
@endsection