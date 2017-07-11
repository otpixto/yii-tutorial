<!-- BEGIN PAGE BREADCRUMBS -->
<ul class="page-breadcrumb breadcrumb">
    @foreach ( $breadcrumbs as $i => $breadcrumb )
        <li>
            @if ( count( $breadcrumb ) == 1 )
                <span class="active">
                    {{ $breadcrumb[0] }}
                </span>
            @else
                <a href="{{ $breadcrumb[1] }}">
                    {{ $breadcrumb[0] }}
                </a>
            @endif
            @if ( $i != ( count( $breadcrumbs ) - 1 ) )
                <i class="fa fa-circle"></i>
            @endif
        </li>
    @endforeach
</ul>
<!-- END PAGE BREADCRUMBS -->