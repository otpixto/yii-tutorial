<div id="errors-message">
    @if ( $errors->count() )
        @foreach ( $errors->all() as $error )
            @include( 'parts.error' )
        @endforeach
    @endif
</div>