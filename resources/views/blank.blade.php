@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', route( 'home' ) ],
        [ 'Пустая страница' ]
    ]) !!}
@endsection

@section( 'content' )



@endsection