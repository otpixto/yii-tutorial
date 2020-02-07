@extends( 'template' )

@section( 'breadcrumbs' )
    {!! \App\Classes\Breadcrumbs::render([
        [ 'Главная', '/' ],
        [ 'Здания', route( 'buildings.index' ) ],
        [ \App\Classes\Title::get() ]
    ]) !!}
@endsection

@section( 'content' )

    @if ( \Auth::user()->can( 'catalog.buildings.edit' ) )

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    Выбрать УО для привязки
                </h3>
            </div>
            <div class="panel-body">
                {!! Form::model( null, [ 'method' => 'post', 'route' => 'buildings.managements.massManagementsAdd', 'class' => 'submit-loading' ] ) !!}
                <input type="hidden" name="buildings" value="{{ $managementBuildingsListString }}">
                <input type="hidden" name="management_id" value="{{ $management_id }}">
                <div class="row">
                    <div class="col-md-12">
                        <select class="mt-multiselect form-control" multiple="multiple" data-label="left"
                                id="managements" name="managements[]">
                            @foreach ( $availableManagements as $management => $arr )
                                <optgroup label="{{ $management }}">
                                    @foreach ( $arr as $management_id => $management_name )
                                        <option value="{{ $management_id }}">
                                            {{ $management_name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row margin-top-15">
                    <div class="col-md-1 col-md-offset-11">
                        {!! Form::submit( 'Привязать', [ 'class' => 'btn btn-success' ] ) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    @else

        @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )

    @endif

@endsection

@section( 'css' )
    <link href="/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css" rel="stylesheet"
          type="text/css"/>
@endsection

@section( 'js' )
    <script src="/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js"
            type="text/javascript"></script>
    <script type="text/javascript">

        $(document)

            .ready(function () {

                $('.mt-multiselect').multiselect({
                    disableIfEmpty: true,
                    enableFiltering: true,
                    includeSelectAllOption: true,
                    enableCaseInsensitiveFiltering: true,
                    enableClickableOptGroups: true,
                    buttonWidth: '100%',
                    maxHeight: '300',
                    buttonClass: 'mt-multiselect btn btn-default',
                    numberDisplayed: 5,
                    nonSelectedText: '-',
                    nSelectedText: ' выбрано',
                    allSelectedText: 'Все',
                    selectAllText: 'Выбрать все',
                    selectAllValue: ''
                });

            })

    </script>
@endsection
