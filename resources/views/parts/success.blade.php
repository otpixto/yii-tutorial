<?php $_success = \Session::flash( 'success', $success ?? null ); ?>
@if ( !empty( $_success ) )
    <div class="alert alert-success" role="alert">
        <button class="close" data-close="alert"></button>
        <span>
            {!! \Session::flash( 'success', $success ?? null ) !!}
        </span>
    </div>
@endif