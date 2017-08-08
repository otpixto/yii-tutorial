@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная' ]
    ]) !!}
@endsection

@section( 'content' )

    <b>{{ \Config::get( 'app.name' ) }}</b> - это збс!

@endsection