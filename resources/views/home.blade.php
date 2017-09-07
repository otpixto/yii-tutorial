@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    <div class="text-center">
        <a href="/files/info.pdf">
            <img src="/images/info.png" />
        </a>
    </div>

@endsection