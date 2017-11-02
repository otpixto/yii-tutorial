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

    @if ( $data['total'] )

        <table class="table table-striped sortable">
            <thead>
            <tr>
                <th>
                    Тип проблемы \ Нименование УО
                </th>
                @foreach ( $managements as $management )
                    <th class="text-center">
                        {{ $management->name }}
                    </th>
                @endforeach
                <th class="text-center info bold">
                    Всего
                </th>
                <th class="text-center" colspan="2">
                    Процент выполнения
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach ( $categories as $category )
                <tr>
                    <td>
                        {{ $category->name }}
                    </td>
                    @foreach ( $managements as $management )
                        <td class="text-center">
                            @if ( isset( $data[ $category->id ], $data[ $category->id ][ $management->id ] ) )
                                {{ $data[ $category->id ][ $management->id ][ 'closed' ] }}
                                /
                                {{ $data[ $category->id ][ $management->id ][ 'total' ] }}
                            @else
                                0 / 0
                            @endif
                        </td>
                    @endforeach
                    <td class="text-center info bold">
                        @if ( isset( $data[ 'category-' . $category->id ] ) )
                            {{ $data[ 'category-' . $category->id ][ 'closed' ] }}
                            /
                            {{ $data[ 'category-' . $category->id ][ 'total' ] }}
                        @else
                            0 / 0
                        @endif
                    </td>
                    <td class="text-right">
                        @if ( isset( $data[ 'category-' . $category->id ] ) )
                            {{ $data[ 'category-' . $category->id ][ 'total' ] ? ceil( $data[ 'category-' . $category->id ][ 'closed' ] * 100 / $data[ 'category-' . $category->id ][ 'total' ] ) : 0 }}%
                        @else
                            0%
                        @endif
                    </td>
                    <td class="hidden-print hidden-md">
                        @if ( isset( $data[ 'category-' . $category->id ] ) )
                            <div class="progress">
                                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ $data[ 'category-' . $category->id ][ 'total' ] ? ceil( $data[ 'category-' . $category->id ][ 'closed' ] * 100 / $data[ 'category-' . $category->id ][ 'total' ] ) : 0 }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $data[ 'category-' . $category->id ][ 'total' ] ? ceil( $data[ 'category-' . $category->id ][ 'closed' ] * 100 / $data[ 'category-' . $category->id ][ 'total' ] ) : 0 }}%">
                                </div>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-right">
                        Всего:
                    </th>
                    @foreach ( $managements as $management )
                        <th class="text-center">
                            @if ( isset( $data[ 'management-' . $management->id ] ) )
                                {{ $data[ 'management-' . $management->id ][ 'closed' ] }}
                                /
                                {{ $data[ 'management-' . $management->id ][ 'total' ] }}
                            @else
                                0 / 0
                            @endif
                        </th>
                    @endforeach
                    <th class="text-center warning bold">
                        {{ $data[ 'closed' ] }}
                        /
                        {{ $data[ 'total' ] }}
                    </th>
                    <th class="text-right" style="width: 30px;">
                        {{ $data[ 'total' ] ? ceil( $data[ 'closed' ] * 100 / $data[ 'total' ] ) : 0 }}%
                    </th>
                    <th style="width: 15%;" class="hidden-print hidden-md">
                        <div class="progress">
                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ $data[ 'total' ] ? ceil( $data[ 'closed' ] * 100 / $data[ 'total' ] ) : 0 }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $data[ 'total' ] ? ceil( $data[ 'closed' ] * 100 / $data[ 'total' ] ) : 0 }}%">
                            </div>
                        </div>
                    </th>
                </tr>
            </tfoot>
        </table>

    @else
        @include( 'parts.error', [ 'error' => 'По Вашему запросу ничего не найдено' ] )
    @endif

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