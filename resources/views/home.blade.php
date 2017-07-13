@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная' ]
    ]) !!}
@endsection

@section( 'content' )

    <div id="calendar" class="has-toolbar"> </div>

@endsection