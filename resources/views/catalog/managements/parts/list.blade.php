@if ( $managements->count() )

    <div class="row">
        <div class="col-md-8">
            {{ $managements->render() }}
        </div>
        <div class="col-md-4 text-right margin-top-10 margin-bottom-10">
            <span class="label label-info">
                Найдено: <b>{{ $managements->total() }}</b>
            </span>
        </div>
    </div>

    <table class="table table-hover table-striped">
        <thead>
        <tr>
            <th width="10%">
                Категория
            </th>
            <th width="20%">
                Наименование
            </th>
            <th>
                Адрес \ телефон(ы)
            </th>
            @if ( \Auth::user()->can( 'catalog.buildings.show' ) )
                <th class="text-center">
                    Адреса
                </th>
            @endif
            @if ( \Auth::user()->can( 'catalog.types.show' ) )
                <th class="text-center">
                    Классификатор
                </th>
            @endif
            @if ( \Auth::user()->can( 'catalog.managements.executors.show' ) )
                <th class="text-center">
                    Исполнители
                </th>
            @endif
            <th class="text-center">
                GUID
            </th>
            <th class="text-center" width="80">
                Есть договор
            </th>
            <th class="text-center" width="80">
                Оповещения в Telegram
            </th>
            <th class="text-right">
                &nbsp;
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach ( $managements as $management )
            <tr>
                <td>
                    {{ $management->getCategory() }}
                </td>
                <td>
                    @if ( $management->parent )
                        <div class="text-muted">
                            {{ $management->parent->name }}
                        </div>
                    @endif
                    {{ $management->name }}
                </td>
                <td>
                    @if ( $management->building )
                        <div>
                            {{ $management->getAddress() }}
                        </div>
                    @endif
                    <div class="margin-top-10">
                        {!! $management->getPhones( true ) !!}
                    </div>
                </td>
                @if ( \Auth::user()->can( 'catalog.buildings.show' ) )
                    <td class="text-center">
                        <a href="{{ route( 'managements.buildings', $management->id ) }}" class="badge badge-{{ $management->buildings()->count() ? 'info' : 'default' }} bold">
                            {{ $management->buildings()->count() }}
                        </a>
                    </td>
                @endif
                @if ( \Auth::user()->can( 'catalog.types.show' ) )
                    <td class="text-center">
                        <a href="{{ route( 'managements.types', $management->id ) }}" class="badge badge-{{ $management->types()->count() ? 'info' : 'default' }} bold">
                            {{ $management->types()->count() }}
                        </a>
                    </td>
                @endif
                @if ( \Auth::user()->can( 'catalog.managements.executors.show' ) )
                    <td class="text-center">
                        <a href="{{ route( 'managements.executors', $management->id ) }}" class="badge badge-{{ $management->executors()->count() ? 'info' : 'default' }} bold">
                            {{ $management->executors()->count() }}
                        </a>
                    </td>
                @endif
                <td class="text-center">
                    @if ( $management->guid )
                        @include( 'parts.yes' )
                    @else
                        @include( 'parts.no' )
                    @endif
                </td>
                <td class="text-center">
                    @if ( $management->has_contract )
                        @include( 'parts.yes' )
                    @else
                        @include( 'parts.no' )
                    @endif
                </td>
                <td class="text-center">
                    @if ( $management->telegram_code )
                        @include( 'parts.yes' )
                    @else
                        @include( 'parts.no' )
                    @endif
                </td>
                <td class="text-right">
                    @if ( \Auth::user()->can( 'catalog.managements.edit' ) )
                        <a href="{{ route( 'managements.edit', $management->id ) }}" class="btn btn-info">
                            <i class="fa fa-edit"></i>
                        </a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $managements->render() }}

@else
    @include( 'parts.error', [ 'error' => 'Ничего не найдено' ] )
@endif