@extends( 'news.template' )

@section( 'content' )

    <div class="portlet light portlet-fit bg-inverse ">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-newspaper-o font-red"></i>
                <span class="caption-subject bold font-red uppercase">
                    {{ \App\Classes\Title::get() }}
                </span>
            </div>
            <div class="actions">
                <a href="{{ route( 'news.rss' ) }}" class="btn red btn-outline btn-circle btn-sm">
                    <i class="fa fa-rss"></i>
                    RSS
                </a>
            </div>
        </div>
        <div class="portlet-body">
            <div class="timeline  white-bg ">
            @foreach ( $news as $r )
                <!-- TIMELINE ITEM -->
                <div class="timeline-item">
                    <div class="timeline-badge">
                        {!! $r->getIconNew() !!}
                    </div>
                    <div class="timeline-body">
                        <div class="timeline-body-arrow"> </div>
                        <div class="row">
                            <div class="col-xs-8">
                                <a href="{{ route( 'news.show', $r->id ) }}" class="timeline-body-title font-blue-madison">
                                    {{ $r->title }}
                                </a>
                            </div>
                            <div class="col-xs-4 text-right">
                                <span class="font-grey-cascade small">
                                    {{ $r->getDateTime() }}
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                {!! $r->body !!}
                            </div>
                        </div>
                        <hr />
                        <div class="row">
                            <div class="col-xs-6">
                                <a href="{{ route( 'news.show', $r->id ) }}" class="btn btn-circle default">
                                    Читать
                                </a>
                            </div>
                            <div class="col-xs-6 text-right">
                                <a href="javascript:;" class="btn btn-circle btn-icon-only default">
                                    <i class="fa fa-twitter"></i>
                                </a>
                                <a href="javascript:;" class="btn btn-circle btn-icon-only default">
                                    <i class="fa fa-vk"></i>
                                </a>
                                <a href="javascript:;" class="btn btn-circle btn-icon-only default">
                                    <i class="fa fa-odnoklassniki"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END TIMELINE ITEM -->
            @endforeach
            </div>
        </div>
    </div>

    <div class="portlet light portlet-fit ">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-newspaper-o font-green"></i>
                <span class="caption-subject bold font-green uppercase">
                    {{ \App\Classes\Title::get() }}
                </span>
            </div>
            <div class="actions">
                <a href="{{ route( 'news.rss' ) }}" class="btn red btn-outline btn-circle btn-sm">
                    <i class="fa fa-rss"></i>
                    RSS
                </a>
            </div>
        </div>
        <div class="portlet-body">
            <div class="mt-timeline-2">
                <div class="mt-timeline-line border-grey-steel"></div>
                <ul class="mt-container">
                    @php( $side = 'left' )
                    @foreach ( $news as $r )
                        <li class="mt-item">
                            {!! $r->getIcon() !!}
                            <div class="mt-timeline-content">
                                <div class="{{ $r->getClass( $side ) }}">
                                    <div class="mt-title">
                                        <h3 class="mt-content-title">{{ $r->title }}</h3>
                                    </div>
                                    <div class="mt-author">
                                        <div class="mt-author-name">
                                            <a href="javascript:;" class="font-white">
                                                {{ $r->author->getShortName() }}
                                            </a>
                                        </div>
                                        <div class="mt-author-notes font-white">
                                            {{ $r->getDateTime() }}
                                        </div>
                                    </div>
                                    <div class="mt-content border-white">
                                        <div class="news-body">
                                            {!! $r->body !!}
                                        </div>
                                        <div class="margin-top-30">
                                            <a href="{{ route( 'news.show', $r->id ) }}" class="btn btn-circle white">
                                                Читать
                                            </a>
                                            <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default pull-right">
                                                <i class="fa fa-twitter"></i>
                                            </a>
                                            <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default pull-right">
                                                <i class="fa fa-vk"></i>
                                            </a>
                                            <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default pull-right">
                                                <i class="fa fa-odnoklassniki"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @php( $side = $side == 'left' ? 'right' : 'left' )
                    @endforeach
                </ul>
            </div>
            @if ( $news->lastPage() > $news->currentPage() )
                <div>
                    <button class="btn btn-primary btn-lg btn-block btn-circle">Загрузить еще</button>
                </div>
            @endif
        </div>
    </div>

@endsection

@section( 'css' )
    <link href="/assets/layouts/layout3/css/themes/default.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/pages/css/news.css" rel="stylesheet" type="text/css" />
@endsection