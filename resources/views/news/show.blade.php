@extends( 'news.template' )

@section( 'content' )

    <div class="portlet light portlet-fit">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-newspaper-o font-blue-madison"></i>
                <span class="caption-subject bold font-blue-madison uppercase">
                    {{ $news->title }}
                </span>
            </div>
            <div class="actions">
                <a href="{{ route( 'news.index' ) }}" class="btn red btn-outline btn-circle btn-sm">
                    Список новостей
                </a>
            </div>
        </div>
        <div class="portlet-body">
            {!! $news->body !!}
        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/layouts/layout3/css/themes/default.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/pages/css/news.css" rel="stylesheet" type="text/css" />
@endsection