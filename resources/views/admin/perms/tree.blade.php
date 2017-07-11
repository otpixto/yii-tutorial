@foreach ( $tree as $t )
    <li class="jstree-open" id="{{ $t->code }}">
        <a href="{{ route( 'perms.edit', $t->id ) }}" class="{{ isset( $role ) && $role->hasPermissionTo( $t->code ) ? 'jstree-clicked' : '' }}">
            <i class="jstree-icon jstree-checkbox" role="presentation"></i>
            {{ $t->name }}
        </a>
        @if ( $t->childs )
            <ul>
                @include( 'admin.perms.tree', [ 'tree' => $t->childs ] )
            </ul>
        @endif
    </li>
@endforeach