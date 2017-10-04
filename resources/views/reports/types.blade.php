@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    {!! Form::open( [ 'method' => 'get', 'class' => 'form-horizontal' ] ) !!}
    <div class="form-group">
        {!! Form::label( 'date_from', 'Период', [ 'class' => 'control-label col-md-3' ] ) !!}
        <div class="col-md-6">
            <div class="col-xs-6">
                {!! Form::text( 'date_from', $date_from, [ 'class' => 'form-control datepicker' ] ) !!}
            </div>
            <div class="col-xs-6">
                {!! Form::text( 'date_to', $date_to, [ 'class' => 'form-control datepicker' ] ) !!}
            </div>
        </div>
        <div class="col-md-3">
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
                Выполнено
            </th>
            <th class="text-center">
                Отменено
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
                        {{ $category['completed'] }}
                    </td>
                    <td data-field="canceled" class="text-center">
                        {{ $category['canceled'] }}
                    </td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
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