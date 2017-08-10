<!-- BEGIN PAGE BREADCRUMBS -->
<ol class="breadcrumb hidden-print">
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
                </li>
        @endforeach
</ol>
<!-- END PAGE BREADCRUMBS -->