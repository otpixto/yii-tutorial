@if ($paginator->hasPages())

    <div class="margin-top-10 margin-bottom-10 visible-print">
        <span class="label label-info">
            Страница <b>{{ $paginator->currentPage() }}</b> / <b>{{ $paginator->lastPage() }}</b>
        </span>
    </div>
    <div class="row">
        <div class="col-md-9">
            <ul class="pagination hidden-print">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="disabled"><span>&laquo;</span></li>
                @else
                    <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a></li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="disabled"><span>{{ $element }}</span></li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="active"><span>{{ $page }}</span></li>
                            @else
                                <li><a href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a></li>
                @else
                    <li class="disabled"><span>&raquo;</span></li>
                @endif
            </ul>
        </div>
        <div class="col-md-3">
            <form class="form-inline">
                <div class="form-group mx-sm-1 mb-1">
                    <input type="text" class="form-control margin-top-10" style="width: 60px;" name="page"
                           placeholder="Стр.">
                </div>
                <button type="submit" class="btn btn-sm btn-primary mb-1 margin-top-10">Поиск</button>
            </form>
        </div>
    </div>

@endif
