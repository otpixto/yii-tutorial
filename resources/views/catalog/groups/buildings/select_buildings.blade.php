<div class="list-group">
    @foreach ( $groups as $group )
        <a href="javascript:;" class="list-group-item" data-group-data="{{ $group->buildings->pluck( 'name', 'id' )->toJson() }}" data-group-selector="{{ $selector }}">
            {{ $group->name }}
        </a>
    @endforeach
</div>