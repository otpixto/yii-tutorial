@foreach ( $works as $work )
    <div class="row">
        <div class="col-xs-6">
            <a href="{{ route( 'works.show', $work->id ) }}" target="_blank" class="bold">
                № {{ $work->id }}
            </a>
        </div>
        <div class="col-xs-6 text-right small text-muted">
            {{ $work->created_at->format( 'd.m.Y H:i' ) }}
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div>
                Исполнитель:
                <strong>
                    @if ( $work->management->parent )
                        <span class="text-muted">
                            {{ $work->management->parent->name }}
                        </span>
                    @endif
                    {{ $work->management->name }}
                </strong>
            </div>
            <div>
                {{ $work->composition }}
            </div>
            <div>
                Период:
                <strong>
                    {{ $work->time_begin->format( 'd.m.Y H:i' ) }}
                    -
                    {{ $work->time_end->format( 'd.m.Y H:i' ) }}
                </strong>
            </div>
        </div>
    </div>
    <hr />
@endforeach
@can ( 'works.show' )
    <div class="margin-top-10 text-right">
        <a href="{{ route( 'works.index' ) }}" target="_blank" class="text-primary">
            Показать все активные отключения
        </a>
    </div>
@endcan