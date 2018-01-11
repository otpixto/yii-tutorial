@component( 'mail::message' )

@if ( ! empty( $message ) )
{!! $message !!}
@endif

@if ( ! empty( $url ) )
@component( 'mail::button', [ 'url' => $url ] )
Перейти к просмотру
@endcomponent
@endif

@endcomponent
