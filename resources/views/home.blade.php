@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная' ]
    ]) !!}
@endsection

@section( 'content' )
    Главная страница и здесь нет нихуя
@endsection