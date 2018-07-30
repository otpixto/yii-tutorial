<div class="row margin-top-15 hidden-print" id="search">
    <div class="col-xs-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-search"></i>
                    ПОИСК
                </div>
                <div class="tools">
                    <a href="javascript:;" class="{{ ! Input::get( 'search' ) ? 'expand' : 'collapse' }}" data-original-title="Показать\Скрыть" title="Показать\Скрыть"> </a>
                </div>
            </div>
            <div class="portlet-body {{ ! Input::get( 'search' ) ? 'portlet-collapsed' : '' }}">

                @include( 'works.parts.search' )

            </div>
        </div>
    </div>
</div>