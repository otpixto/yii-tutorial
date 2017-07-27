@if ( \Session::has( 'success' ) )
    <div class="alert alert-success" role="alert">
        <button class="close" data-close="alert"></button>
        <span>
            {!! \Session::get( 'success' ) !!}
        </span>
    </div>
@endif