<div id="success-message">
    @if ( \Session::has( 'success' ) )
        <div class="alert alert-success margin-bottom-15 hidden-print" role="alert">
            <button class="close" data-close="alert"></button>
            <span>
                <i class="fa fa-check"></i>
                {!! \Session::get( 'success' ) !!}
            </span>
        </div>
    @endif
</div>