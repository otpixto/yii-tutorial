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
            <th>
                Категория проблем
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
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>

@endsection