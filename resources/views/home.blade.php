@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <b>{{ \Config::get( 'app.name' ) }}</b> - это збс!

@endsection