@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <table class="table table-hover table-striped sortable">
        <thead>
            <tr>
                <th>
                    Нименование ЭО
                </th>
                <th class="text-center">
                    Количество заявок
                </th>
                <th class="text-center">
                    Количество выполненных заявок
                </th>
            </tr>
        </thead>
        <tbody>
        @foreach ( $data as $r )
            <tr>
                <td>
                    {{ $r['name'] }}
                </td>
                <td class="text-center">
                    {{ $r['total'] }}
                </td>
                <td class="text-center">
                    {{ $r['completed'] }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection